<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\AttendanceLog;
use App\Models\DeviceCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmsProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_handshake_registers_device_and_returns_config(): void
    {
        $response = $this->get('/iclock/cdata?SN=MB20VL-DEMO-01&pushver=2.4.1&language=69');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('GET OPTION FROM: MB20VL-DEMO-01', $response->getContent());
        $this->assertStringContainsString('Realtime=1', $response->getContent());

        $this->assertDatabaseHas('devices', [
            'sn' => 'MB20VL-DEMO-01',
            'push_version' => '2.4.1',
        ]);
    }

    public function test_device_can_push_attendance_logs_tab_separated(): void
    {
        // 1. Handshake inicial
        $this->get('/iclock/cdata?SN=MB20VL-DEMO-01');

        // 2. Enviar fichada
        $payload = "1055\t2026-09-03 08:30:15\t0\t15\t0\t0\t0\n1056\t2026-09-03 08:31:00\t1\t1\t0\t0\t0\n";
        $response = $this->call('POST', '/iclock/cdata?SN=MB20VL-DEMO-01&table=ATTLOG', [], [], [], [
            'CONTENT_TYPE' => 'text/plain'
        ], $payload);

        $response->assertStatus(200);
        $this->assertEquals('OK: 2', trim($response->getContent()));

        $this->assertDatabaseHas('attendance_logs', [
            'device_sn' => 'MB20VL-DEMO-01',
            'user_pin' => '1055',
            'status' => 0,
            'verify_type' => 15,
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'device_sn' => 'MB20VL-DEMO-01',
            'user_pin' => '1056',
            'status' => 1,
            'verify_type' => 1,
        ]);
    }

    public function test_device_can_poll_commands(): void
    {
        // Sin comandos
        $response = $this->get('/iclock/getrequest?SN=MB20VL-DEMO-01');
        $response->assertStatus(200);
        $this->assertEquals('OK', trim($response->getContent()));

        // Con comando en cola
        $cmd = DeviceCommand::create([
            'device_sn' => 'MB20VL-DEMO-01',
            'command' => 'INFO',
            'status' => 'pending',
        ]);

        $responseWithCmd = $this->get('/iclock/getrequest?SN=MB20VL-DEMO-01');
        $responseWithCmd->assertStatus(200);
        $this->assertStringContainsString("C:{$cmd->id}:INFO", $responseWithCmd->getContent());

        // Verificar que el comando pasó a estado 'sent'
        $this->assertEquals('sent', $cmd->fresh()->status);
    }

    public function test_smart_status_alternates_checkin_and_checkout(): void
    {
        $this->get('/iclock/cdata?SN=MB20VL-DEMO-01');

        // Primer fichaje -> Entrada (0)
        $payload1 = "2001\t2026-09-03 08:00:00\t0\t15\t0\t0\t0\n";
        $this->call('POST', '/iclock/cdata?SN=MB20VL-DEMO-01&table=ATTLOG', [], [], [], [
            'CONTENT_TYPE' => 'text/plain'
        ], $payload1);

        $this->assertDatabaseHas('attendance_logs', [
            'user_pin' => '2001',
            'punch_time' => '2026-09-03 08:00:00',
            'status' => 0, // Entrada
        ]);

        // Segundo fichaje enviado como 0 por el reloj -> debe convertirse inteligentemente a Salida (1)
        $payload2 = "2001\t2026-09-03 14:00:00\t0\t15\t0\t0\t0\n";
        $this->call('POST', '/iclock/cdata?SN=MB20VL-DEMO-01&table=ATTLOG', [], [], [], [
            'CONTENT_TYPE' => 'text/plain'
        ], $payload2);

        $this->assertDatabaseHas('attendance_logs', [
            'user_pin' => '2001',
            'punch_time' => '2026-09-03 14:00:00',
            'status' => 1, // Salida
        ]);
    }
}
