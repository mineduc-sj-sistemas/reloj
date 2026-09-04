<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AttendanceLog;
use App\Models\DeviceCommand;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    /**
     * Handshake inicial / Heartbeat de configuración del ZKTeco MB20-VL
     * GET /iclock/cdata?SN=...
     */
    public function handshake(Request $request)
    {
        $sn = $request->query('SN');
        if (!$sn) {
            return response("UNKNOWN DEVICE", 400)->header('Content-Type', 'text/plain');
        }

        $pushver = $request->query('pushver', '2.4.1');
        $language = $request->query('language', '69');

        // Registrar o actualizar dispositivo
        $device = Device::updateOrCreate(
            ['sn' => $sn],
            [
                'ip_address' => $request->ip(),
                'push_version' => $pushver,
                'last_activity' => now(),
                'status' => 'online',
            ]
        );

        Log::info("ADMS: Handshake recibido del dispositivo SN: {$sn} (IP: {$request->ip()})");

        // Opciones de configuración que el reloj espera recibir
        $options = [
            "GET OPTION FROM: {$sn}",
            "Stamp=9999",
            "OpStamp=0",
            "PhotoStamp=0",
            "ErrorDelay=60",
            "Delay=30",
            "TransTimes=00:00;14:05",
            "TransInterval=1",
            "TransFlag=1111000000",
            "TimeZone=0",
            "Realtime=1",
            "Encrypt=0",
            "ServerVersion=2.4.1"
        ];

        return response(implode("\n", $options) . "\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Recepción de datos (Fichajes / Logs de Asistencia / Operaciones)
     * POST /iclock/cdata?SN=...&table=ATTLOG
     */
    public function receiveData(Request $request)
    {
        $sn = $request->query('SN');
        $table = strtoupper($request->query('table', 'ATTLOG'));

        if (!$sn) {
            return response("NO SN", 400)->header('Content-Type', 'text/plain');
        }

        // Actualizar último contacto del dispositivo
        Device::updateOrCreate(
            ['sn' => $sn],
            [
                'ip_address' => $request->ip(),
                'last_activity' => now(),
                'status' => 'online',
            ]
        );

        $content = $request->getContent();
        if (empty(trim($content))) {
            return response("OK", 200)->header('Content-Type', 'text/plain');
        }

        Log::info("ADMS: Datos recibidos de {$sn} [Tabla: {$table}]:\n" . substr($content, 0, 500));

        $count = 0;

        if ($table === 'ATTLOG') {
            $count = $this->processAttendanceLogs($sn, $content);
        } elseif ($table === 'USER' || $table === 'USERINFO') {
            $count = $this->processUserLogs($content);
        } elseif ($table === 'OPERLOG') {
            Log::info("ADMS: OPERLOG recibido de {$sn}");
            $count = 1;
        } else {
            Log::info("ADMS: Otra tabla ({$table}) recibida de {$sn}");
            $count = 1;
        }

        // Actualizar contador de fichajes del reloj
        $totalAtt = AttendanceLog::where('device_sn', $sn)->count();
        Device::where('sn', $sn)->update(['att_count' => $totalAtt]);

        return response("OK: {$count}", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Entrega de comandos pendientes al dispositivo
     * GET /iclock/getrequest?SN=...
     */
    public function getCommands(Request $request)
    {
        $sn = $request->query('SN');
        if (!$sn) {
            return response("OK", 200)->header('Content-Type', 'text/plain');
        }

        Device::where('sn', $sn)->update([
            'ip_address' => $request->ip(),
            'last_activity' => now(),
        ]);

        $pendingCommands = DeviceCommand::where('device_sn', $sn)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        if ($pendingCommands->isEmpty()) {
            return response("OK", 200)->header('Content-Type', 'text/plain');
        }

        $cmdStrings = [];
        foreach ($pendingCommands as $cmd) {
            $cmdId = $cmd->command_id ?: $cmd->id;
            $cmdStrings[] = "C:{$cmdId}:{$cmd->command}";
            $cmd->update(['status' => 'sent']);
        }

        Log::info("ADMS: Enviando " . count($cmdStrings) . " comandos a {$sn}");

        return response(implode("\n", $cmdStrings) . "\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Respuesta/Confirmación de ejecución de comando por parte del dispositivo
     * POST /iclock/devicecmd?SN=...
     */
    public function deviceCmdResponse(Request $request)
    {
        $sn = $request->query('SN');
        $content = $request->getContent();

        Log::info("ADMS: Respuesta a comando de {$sn}: {$content}");

        // El payload suele tener formato: ID=1&Return=0&CMD=INFO
        $lines = explode("\n", str_replace("\r", "", $content));
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            parse_str($line, $params);
            if (isset($params['ID'])) {
                $cmdId = $params['ID'];
                $returnCode = $params['Return'] ?? null;
                $status = ($returnCode === '0' || $returnCode === 0) ? 'executed' : 'failed';

                DeviceCommand::where('device_sn', $sn)
                    ->where(function ($q) use ($cmdId) {
                        $q->where('command_id', $cmdId)->orWhere('id', $cmdId);
                    })
                    ->update([
                        'status' => $status,
                        'response' => $line,
                        'executed_at' => now(),
                    ]);
            }
        }

        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Endpoint comodín para otras solicitudes ADMS (ej. fdata, ping)
     */
    public function fallback(Request $request)
    {
        $sn = $request->query('SN');
        if ($sn) {
            Device::where('sn', $sn)->update(['last_activity' => now()]);
        }
        return response("OK", 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Procesa líneas de texto plano de ATTLOG
     */
    private function processAttendanceLogs(string $sn, string $content): int
    {
        $lines = explode("\n", str_replace("\r", "", $content));
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Caso 1: Formato separado por tabulaciones o espacios múltiples
            // Formato estándar: PIN \t Date_Time \t Status \t VerifyType \t WorkCode \t Reserved1 \t Reserved2
            // Ejemplo: 1001\t2026-09-03 08:30:15\t0\t15\t0\t0\t0
            $parts = preg_split('/\t+|\s{2,}/', $line);

            if (count($parts) >= 2) {
                $pin = trim($parts[0]);
                $punchTimeRaw = trim($parts[1]);
                $status = isset($parts[2]) ? (int)trim($parts[2]) : 0;
                $verifyType = isset($parts[3]) ? (int)trim($parts[3]) : 0;
                $workCode = isset($parts[4]) ? trim($parts[4]) : null;
                $reserved1 = isset($parts[5]) ? trim($parts[5]) : null;
                $reserved2 = isset($parts[6]) ? trim($parts[6]) : null;

                try {
                    $punchTime = Carbon::parse($punchTimeRaw);
                    $smartStatus = $this->resolveSmartStatus($pin, $punchTime, $status);

                    AttendanceLog::updateOrCreate(
                        [
                            'device_sn' => $sn,
                            'user_pin' => $pin,
                            'punch_time' => $punchTime,
                        ],
                        [
                            'status' => $smartStatus,
                            'verify_type' => $verifyType,
                            'work_code' => $workCode,
                            'reserved_1' => $reserved1,
                            'reserved_2' => $reserved2,
                            'raw_data' => $line,
                        ]
                    );

                    // Asegurar que exista el empleado para este PIN
                    Employee::firstOrCreate(
                        ['pin' => $pin],
                        ['name' => "Agente #{$pin}"]
                    );

                    $count++;
                } catch (\Exception $e) {
                    Log::error("ADMS: Error parseando fecha '{$punchTimeRaw}': " . $e->getMessage());
                }
            } elseif (str_contains($line, '=')) {
                // Caso 2: Formato Clave-Valor (PIN=1001\tTIME=2026-09-03 08:30:15...)
                $items = preg_split('/\t+|\s+/', $line);
                $data = [];
                foreach ($items as $item) {
                    if (str_contains($item, '=')) {
                        [$k, $v] = explode('=', $item, 2);
                        $data[strtoupper(trim($k))] = trim($v);
                    }
                }

                if (isset($data['PIN']) && isset($data['TIME'])) {
                    try {
                        AttendanceLog::updateOrCreate(
                            [
                                'device_sn' => $sn,
                                'user_pin' => $data['PIN'],
                                'punch_time' => Carbon::parse($data['TIME']),
                            ],
                            [
                                'status' => (int)($data['STATUS'] ?? 0),
                                'verify_type' => (int)($data['VERIFY'] ?? 0),
                                'raw_data' => $line,
                            ]
                        );

                        Employee::firstOrCreate(
                            ['pin' => $data['PIN']],
                            ['name' => "Agente #" . $data['PIN']]
                        );

                        $count++;
                    } catch (\Exception $e) {
                        Log::error("ADMS: Error parseando clave-valor: " . $e->getMessage());
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Procesa datos de usuarios (USER / USERINFO) enviados por el reloj
     */
    private function processUserLogs(string $content): int
    {
        Log::info("ADMS: Procesando tabla de usuarios:\n" . $content);
        $lines = explode("\n", str_replace("\r", "", $content));
        $count = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Formato clave-valor: PIN=1\tName=Prueba\tPri=0\tPasswd=... o USER PIN=1\tName=...
            if (str_contains($line, '=')) {
                $items = preg_split('/\t+|\s{2,}/', $line);
                $data = [];
                foreach ($items as $item) {
                    $item = trim($item);
                    // Limpiar prefijo 'USER ' si existe
                    if (str_starts_with(strtoupper($item), 'USER ')) {
                        $item = substr($item, 5);
                    }
                    if (str_contains($item, '=')) {
                        [$k, $v] = explode('=', $item, 2);
                        $data[strtoupper(trim($k))] = trim($v);
                    }
                }

                $pin = $data['PIN'] ?? $data['USERID'] ?? $data['ID'] ?? null;
                $name = $data['NAME'] ?? null;

                if ($pin) {
                    Employee::updateOrCreate(
                        ['pin' => $pin],
                        [
                            'name' => $name ?: ("Agente #" . $pin),
                            'privilege' => (int)($data['PRI'] ?? $data['PRIVILEGE'] ?? 0),
                            'card_number' => $data['CARD'] ?? $data['CARDNO'] ?? null,
                            'password' => $data['PASSWD'] ?? $data['PASSWORD'] ?? null,
                        ]
                    );
                    $count++;
                }
            } else {
                // Formato posicional tabulado: PIN \t Name \t Password \t Card \t Privilege
                $parts = preg_split('/\t+/', $line);
                if (count($parts) >= 2) {
                    $pin = trim($parts[0]);
                    $name = trim($parts[1]);

                    if (!empty($pin)) {
                        Employee::updateOrCreate(
                            ['pin' => $pin],
                            [
                                'name' => !empty($name) ? $name : ("Agente #" . $pin),
                                'password' => isset($parts[2]) ? trim($parts[2]) : null,
                                'card_number' => isset($parts[3]) ? trim($parts[3]) : null,
                                'privilege' => isset($parts[4]) ? (int)trim($parts[4]) : 0,
                            ]
                        );
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Determina de forma inteligente si la marcación es Entrada (0) o Salida (1)
     */
    private function resolveSmartStatus(string $pin, Carbon $punchTime, int $rawStatus): int
    {
        // Si el usuario presionó explícitamente una tecla especial de estado (Salida=1, Descanso=2,3, Extra=4,5)
        if ($rawStatus !== 0) {
            return $rawStatus;
        }

        // Buscar el fichaje inmediatamente anterior de este agente en las últimas 14 horas
        $previousLog = AttendanceLog::where('user_pin', $pin)
            ->where('punch_time', '<', $punchTime)
            ->where('punch_time', '>=', $punchTime->copy()->subHours(14))
            ->orderBy('punch_time', 'desc')
            ->first();

        if (!$previousLog) {
            // Primer fichaje del turno -> Entrada
            return 0;
        }

        // Si el fichaje anterior fue Entrada (0), este pasa a ser Salida (1)
        if ($previousLog->status === 0) {
            return 1;
        }

        // Si el fichaje anterior fue Salida (1), este pasa a ser Entrada (0)
        return 0;
    }
}
