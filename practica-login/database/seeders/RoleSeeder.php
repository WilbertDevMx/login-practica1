<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Seeder para crear los roles base del sistema.
 *
 * Crea los roles: invitado, usuario y administrador.
 * Utiliza firstOrCreate() para evitar duplicados al ejecutar el seeder múltiples veces.
 */
class RoleSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de roles.
     *
     * @return void
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'invitado']);
        Role::firstOrCreate(['name' => 'usuario']);
        Role::firstOrCreate(['name' => 'administrador']);
    }
}