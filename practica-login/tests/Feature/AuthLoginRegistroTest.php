<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\RegistrationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthLoginRegistroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        Role::create(['name' => 'usuario',       'guard_name' => 'web']);
        Role::create(['name' => 'invitado',      'guard_name' => 'web']);

        // Simular reCAPTCHA válido en todos los tests (wildcard para capturar withOptions())
        Http::fake([
            '*' => Http::response(['success' => true]),
        ]);
    }

    // --- Vistas ---

    public function test_formulario_login_es_accesible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_formulario_registro_es_accesible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    // --- Login: validaciones ---

    public function test_login_falla_sin_email(): void
    {
        $response = $this->post('/login', [
            'password'             => 'Password1@',
            'g-recaptcha-response' => 'token-valido',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_falla_sin_password(): void
    {
        $response = $this->post('/login', [
            'email'                => 'usuario@ejemplo.com',
            'g-recaptcha-response' => 'token-valido',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'real@ejemplo.com']);

        $response = $this->post('/login', [
            'email'                => 'real@ejemplo.com',
            'password'             => 'contraseña-incorrecta',
            'g-recaptcha-response' => 'token-valido',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_fallido_registra_login_log(): void
    {
        User::factory()->create(['email' => 'real@ejemplo.com']);

        $this->post('/login', [
            'email'                => 'real@ejemplo.com',
            'password'             => 'contraseña-incorrecta',
            'g-recaptcha-response' => 'token-valido',
        ]);

        $this->assertDatabaseHas('login_logs', [
            'email'   => 'real@ejemplo.com',
            'exitoso' => false,
        ]);
    }

    public function test_logout_cierra_sesion_y_redirige_al_login(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/cerrar-sesion-ahora');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // --- Registro: validaciones ---

    public function test_registro_falla_sin_nombre(): void
    {
        $response = $this->post('/register', [
            'email'                 => 'nuevo@ejemplo.com',
            'password'              => 'Password1@valid',
            'password_confirmation' => 'Password1@valid',
            'g-recaptcha-response'  => 'token-valido',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_registro_falla_con_email_duplicado(): void
    {
        User::factory()->create(['email' => 'existente@ejemplo.com']);

        $response = $this->post('/register', [
            'name'                  => 'Nuevo Usuario',
            'email'                 => 'existente@ejemplo.com',
            'password'              => 'Password1@valid',
            'password_confirmation' => 'Password1@valid',
            'g-recaptcha-response'  => 'token-valido',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registro_falla_con_password_sin_mayusculas(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Nuevo Usuario',
            'email'                 => 'nuevo@ejemplo.com',
            'password'              => 'sinmayusculas1@',
            'password_confirmation' => 'sinmayusculas1@',
            'g-recaptcha-response'  => 'token-valido',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registro_falla_con_password_menor_a_12_caracteres(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Nuevo Usuario',
            'email'                 => 'nuevo@ejemplo.com',
            'password'              => 'Corta1@',
            'password_confirmation' => 'Corta1@',
            'g-recaptcha-response'  => 'token-valido',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registro_fallido_registra_registration_log(): void
    {
        $this->post('/register', [
            'name'                  => '',
            'email'                 => 'nuevo@ejemplo.com',
            'password'              => 'Password1@valid',
            'password_confirmation' => 'Password1@valid',
            'g-recaptcha-response'  => 'token-valido',
        ]);

        $this->assertDatabaseHas('registration_logs', [
            'email'      => 'nuevo@ejemplo.com',
            'successful' => false,
        ]);
    }

    public function test_usuario_autenticado_es_redirigido_desde_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect();
    }
}
