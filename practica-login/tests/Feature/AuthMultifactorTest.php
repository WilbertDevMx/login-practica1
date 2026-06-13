<?php

namespace Tests\Feature;

use App\Models\TwoFactorLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthMultifactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        Role::create(['name' => 'usuario',       'guard_name' => 'web']);
        Role::create(['name' => 'invitado',      'guard_name' => 'web']);
    }

    // --- Vista 2FA ---

    public function test_formulario_2fa_requiere_autenticacion(): void
    {
        $response = $this->get('/2fa/verify');
        $response->assertRedirect('/login');
    }

    public function test_usuario_sin_secret_ve_formulario_de_setup(): void
    {
        $user = User::factory()->create(['google2fa_secret' => null]);
        $this->actingAs($user);

        $response = $this->get('/2fa/verify');

        $response->assertStatus(200);
        $response->assertViewIs('auth.2fa_verify');
        $response->assertViewHas('isNew', true);
        $response->assertViewHas('qrCode');
        $response->assertViewHas('secret');
    }

    public function test_usuario_con_secret_ve_formulario_de_verificacion(): void
    {
        $user = User::factory()->create(['google2fa_secret' => 'SECRETOBASE32VALIDO']);
        $this->actingAs($user);

        $response = $this->get('/2fa/verify');

        $response->assertStatus(200);
        $response->assertViewHas('isNew', false);
    }

    public function test_2fa_ya_completado_redirige_al_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->withSession(['auth.2fa.completed' => true]);

        $response = $this->get('/2fa/verify');

        $response->assertRedirect('/dashboard');
    }

    // --- Verificación 2FA ---

    public function test_verificacion_2fa_sin_otp_falla_validacion(): void
    {
        $user = User::factory()->create(['google2fa_secret' => 'SECRETOBASE32VALIDO']);
        $this->actingAs($user);

        $response = $this->post('/2fa/verify', []);

        $response->assertSessionHasErrors('one_time_password');
    }

    public function test_otp_invalido_registra_two_factor_log_fallido(): void
    {
        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();

        $user = User::factory()->create(['google2fa_secret' => $secret]);
        $this->actingAs($user);

        $this->post('/2fa/verify', ['one_time_password' => '000000']);

        $this->assertDatabaseHas('two_factor_logs', [
            'user_id'    => $user->id,
            'action'     => 'verify_attempt',
            'successful' => false,
        ]);
    }

    public function test_otp_invalido_retorna_error_al_formulario(): void
    {
        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();

        $user = User::factory()->create(['google2fa_secret' => $secret]);
        $this->actingAs($user);

        $response = $this->post('/2fa/verify', ['one_time_password' => '000000']);

        $response->assertSessionHasErrors('one_time_password');
    }

    public function test_otp_valido_registra_two_factor_log_exitoso(): void
    {
        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();
        $otp       = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['google2fa_secret' => $secret]);
        $user->assignRole('usuario');
        $this->actingAs($user);

        $this->post('/2fa/verify', ['one_time_password' => $otp]);

        $this->assertDatabaseHas('two_factor_logs', [
            'user_id'    => $user->id,
            'action'     => 'verify_attempt',
            'successful' => true,
        ]);
    }

    public function test_sin_secret_configurado_retorna_error(): void
    {
        $user = User::factory()->create(['google2fa_secret' => null]);
        $this->actingAs($user);

        // Sin secret en sesión ni en modelo
        $response = $this->post('/2fa/verify', ['one_time_password' => '123456']);

        $response->assertSessionHasErrors('one_time_password');
    }

    // --- Middleware EnsureMultiFactorComplete ---

    public function test_middleware_bloquea_dashboard_sin_2fa_completado(): void
    {
        $user = User::factory()->create();
        $user->assignRole('usuario');
        $this->actingAs($user);

        $response = $this->get('/dashboard/usuario');

        $response->assertRedirect('/2fa/verify');
    }

    public function test_middleware_bloquea_admin_dashboard_sin_3fa(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrador');
        $this->actingAs($user)
             ->withSession(['auth.2fa.completed' => true]);

        $response = $this->get('/dashboard/admin');

        $response->assertRedirect('/3fa/verify');
    }

    public function test_middleware_invitado_accede_a_su_dashboard_sin_mfa(): void
    {
        $user = User::factory()->create();
        $user->assignRole('invitado');
        $this->actingAs($user);

        $response = $this->get('/dashboard/invitado');

        $response->assertStatus(200);
    }

    public function test_middleware_redirige_usuario_si_intenta_acceder_a_dashboard_ajeno(): void
    {
        $user = User::factory()->create();
        $user->assignRole('usuario');
        $this->actingAs($user)
             ->withSession(['auth.2fa.completed' => true]);

        // Un usuario intenta acceder al dashboard de admin
        $response = $this->get('/dashboard/admin');

        $response->assertRedirect('/dashboard/usuario');
    }
}
