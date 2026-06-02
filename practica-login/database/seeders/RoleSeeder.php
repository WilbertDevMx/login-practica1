<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles solo si no existen para evitar errores al ejecutar el seeder varias veces
        Role::firstOrCreate(['name' => 'invitado']);
        Role::firstOrCreate(['name' => 'usuario']);
        Role::firstOrCreate(['name' => 'administrador']);

    }
}
