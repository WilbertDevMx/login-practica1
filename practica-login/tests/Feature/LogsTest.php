<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\RegistrationLog;
use App\Models\TwoFactorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogsTest extends TestCase
{
    use RefreshDatabase;

    // --- LoginLog ---

    public function test_login_log_se_crea_con_intento_exitoso(): void
    {
        $log = LoginLog::create([
            'email'      => 'usuario@ejemplo.com',
            'ip'         => '127.0.0.1',
            'exitoso'    => true,
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'email'   => 'usuario@ejemplo.com',
            'exitoso' => true,
        ]);
        $this->assertNotNull($log->id);
    }

    public function test_login_log_se_crea_con_intento_fallido(): void
    {
        LoginLog::create([
            'email'   => 'malo@ejemplo.com',
            'ip'      => '10.0.0.1',
            'exitoso' => false,
        ]);

        $this->assertDatabaseHas('login_logs', [
            'email'   => 'malo@ejemplo.com',
            'exitoso' => false,
        ]);
    }

    public function test_login_log_user_agent_es_opcional(): void
    {
        $log = LoginLog::create([
            'email'   => 'sin-agent@ejemplo.com',
            'ip'      => '192.168.1.1',
            'exitoso' => true,
        ]);

        $this->assertNull($log->user_agent);
    }

    // --- RegistrationLog ---

    public function test_registration_log_se_crea_con_usuario(): void
    {
        $user = User::factory()->create();

        $log = RegistrationLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => '127.0.0.1',
            'successful' => true,
        ]);

        $this->assertDatabaseHas('registration_logs', [
            'user_id'    => $user->id,
            'successful' => true,
        ]);
    }

    public function test_registration_log_tiene_relacion_con_usuario(): void
    {
        $user = User::factory()->create();

        $log = RegistrationLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => '127.0.0.1',
            'successful' => true,
        ]);

        $this->assertTrue($log->user->is($user));
    }

    public function test_registration_log_user_id_es_nullable(): void
    {
        $log = RegistrationLog::create([
            'user_id'    => null,
            'email'      => 'fallido@ejemplo.com',
            'ip'         => '127.0.0.1',
            'successful' => false,
            'message'    => 'Email ya registrado',
        ]);

        $this->assertNull($log->user_id);
        $this->assertEquals('Email ya registrado', $log->message);
    }

    // --- TwoFactorLog ---

    public function test_two_factor_log_se_crea_con_accion_setup(): void
    {
        $user = User::factory()->create();

        TwoFactorLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => '127.0.0.1',
            'action'     => 'setup',
            'successful' => true,
        ]);

        $this->assertDatabaseHas('two_factor_logs', [
            'user_id' => $user->id,
            'action'  => 'setup',
        ]);
    }

    public function test_two_factor_log_registra_intento_fallido(): void
    {
        $user = User::factory()->create();

        TwoFactorLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => '127.0.0.1',
            'action'     => 'verify_attempt',
            'successful' => false,
            'message'    => 'Código incorrecto',
        ]);

        $this->assertDatabaseHas('two_factor_logs', [
            'user_id'    => $user->id,
            'successful' => false,
            'message'    => 'Código incorrecto',
        ]);
    }

    public function test_two_factor_log_se_elimina_en_cascada_con_usuario(): void
    {
        $user = User::factory()->create();

        TwoFactorLog::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'ip'         => '127.0.0.1',
            'action'     => 'verify_attempt',
            'successful' => true,
        ]);

        $userId = $user->id;
        $user->delete();

        $this->assertDatabaseMissing('two_factor_logs', ['user_id' => $userId]);
    }
}
