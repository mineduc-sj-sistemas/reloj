<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateZkDevice extends Command
{
    protected $signature = 'zk:simulate {--sn=MB20VL-TEST-01 : Número de serie del reloj ZKTeco} {--pin=1001 : PIN/Legajo del usuario} {--count=1 : Cantidad de marcaciones a generar}';
    protected $description = 'Simula un reloj ZKTeco MB20-VL enviando paquetes ADMS PUSH al servidor local';

    public function handle()
    {
        $sn = $this->option('sn');
        $pin = $this->option('pin');
        $count = (int)$this->option('count');
        $baseUrl = config('app.url', 'http://localhost:8000');

        $this->info("Iniciando simulación de reloj ZKTeco MB20-VL (SN: {$sn}) hacia {$baseUrl}...");

        // 1. Handshake inicial
        $this->line("1. Enviando Handshake inicial (GET /iclock/cdata)...");
        try {
            $handshakeRes = Http::get("{$baseUrl}/iclock/cdata", [
                'SN' => $sn,
                'options' => 'all',
                'pushver' => '2.4.1',
                'language' => '69',
            ]);

            $this->info("Respuesta Handshake:\n" . $handshakeRes->body());
        } catch (\Exception $e) {
            $this->error("Error conectando con el servidor en {$baseUrl}: " . $e->getMessage());
            $this->warn("Asegúrate de que 'php artisan serve' esté corriendo.");
            return 1;
        }

        // 2. Fichadas
        $this->line("2. Generando {$count} fichada(s) de asistencia (POST /iclock/cdata)...");
        $payloadLines = [];
        for ($i = 0; $i < $count; $i++) {
            $currentPin = $pin + $i;
            $punchTime = now()->subMinutes(($count - $i) * 5)->format('Y-m-d H:i:s');
            // Formato: PIN \t Time \t Status (0=CheckIn) \t VerifyType (15=Face) \t WorkCode \t R1 \t R2
            $payloadLines[] = "{$currentPin}\t{$punchTime}\t0\t15\t0\t0\t0";
        }

        $body = implode("\n", $payloadLines) . "\n";

        $postRes = Http::withBody($body, 'text/plain')
            ->post("{$baseUrl}/iclock/cdata?SN={$sn}&table=ATTLOG");

        $this->info("Respuesta de recepción de datos: " . $postRes->body());

        // 3. Polling de comandos
        $this->line("3. Consultando comandos pendientes (GET /iclock/getrequest)...");
        $cmdRes = Http::get("{$baseUrl}/iclock/getrequest", ['SN' => $sn]);
        $this->info("Respuesta Comandos: " . $cmdRes->body());

        $this->newLine();
        $this->info("✔ Simulación completada con éxito. Puedes abrir {$baseUrl} en tu navegador para ver los registros.");
        return 0;
    }
}
