<?php

namespace Tests\Feature;

use App\Models\RoleChangeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles necesarios para los tests
        Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        Role::create(['name' => 'usuario',       'guard_name' => 'web']);
        Role::create(['name' => 'invitado',      'guard_name' => 'web']);
    }

    // --- Control de acceso ---

    public function test_usuario_no_autenticado_no_puede_cambiar_roles(): void
    {
        $target = User::factory()->create();
        $target->assignRole('invitado');

        $response = $this->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $response->assertRedirect('/login');
    }

    public function test_usuario_con_rol_usuario_recibe_403(): void
    {
        $actor  = User::factory()->create();
        $actor->assignRole('usuario');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $response = $this->actingAs($actor)
                         ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $response->assertStatus(403);
    }

    public function test_invitado_recibe_403_al_intentar_cambiar_rol(): void
    {
        $actor  = User::factory()->create();
        $actor->assignRole('invitado');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $response = $this->actingAs($actor)
                         ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $response->assertStatus(403);
    }

    // --- Cambio de rol exitoso ---

    public function test_admin_puede_cambiar_rol_de_otro_usuario(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('administrador');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $this->actingAs($admin)
             ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $this->assertTrue($target->fresh()->hasRole('usuario'));
    }

    public function test_cambio_de_rol_crea_role_change_log(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('administrador');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $this->actingAs($admin)
             ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $this->assertDatabaseHas('role_change_logs', [
            'admin_id'       => $admin->id,
            'target_user_id' => $target->id,
            'rol_anterior'   => 'invitado',
            'rol_nuevo'      => 'usuario',
        ]);
    }

    public function test_cambio_de_rol_retorna_mensaje_de_exito(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('administrador');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $response = $this->actingAs($admin)
                         ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $response->assertSessionHas('success');
    }

    // --- Restricciones de negocio ---

    public function test_admin_no_puede_cambiar_su_propio_rol(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $response = $this->actingAs($admin)
                         ->put("/admin/users/{$admin->id}/rol", ['rol' => 'usuario']);

        $response->assertSessionHas('error', 'No puedes cambiar tu propio rol.');
        $this->assertTrue($admin->fresh()->hasRole('administrador'));
    }

    public function test_no_se_registra_log_si_el_rol_es_el_mismo(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('administrador');

        $target = User::factory()->create();
        $target->assignRole('usuario');

        $this->actingAs($admin)
             ->put("/admin/users/{$target->id}/rol", ['rol' => 'usuario']);

        $this->assertDatabaseMissing('role_change_logs', [
            'target_user_id' => $target->id,
        ]);
    }

    public function test_rol_invalido_falla_validacion(): void
    {
        $admin  = User::factory()->create();
        $admin->assignRole('administrador');

        $target = User::factory()->create();
        $target->assignRole('invitado');

        $response = $this->actingAs($admin)
                         ->put("/admin/users/{$target->id}/rol", ['rol' => 'rol-inexistente']);

        $response->assertSessionHasErrors('rol');
    }

    // --- RoleChangeLog ---

    public function test_role_change_log_almacena_datos_correctos(): void
    {
        $log = RoleChangeLog::create([
            'admin_id'       => 1,
            'admin_email'    => 'admin@ejemplo.com',
            'target_user_id' => 2,
            'target_email'   => 'usuario@ejemplo.com',
            'rol_anterior'   => 'invitado',
            'rol_nuevo'      => 'usuario',
            'ip'             => '127.0.0.1',
        ]);

        $this->assertEquals('invitado', $log->rol_anterior);
        $this->assertEquals('usuario',  $log->rol_nuevo);
        $this->assertNotNull($log->id);
    }
}
