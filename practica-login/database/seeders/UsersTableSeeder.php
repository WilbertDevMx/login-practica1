<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para poblar la tabla 'users' con usuarios de prueba.
 *
 * Crea un usuario por cada rol: invitado, usuario y administrador.
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de usuarios.
     *
     * @return void
     */
    public function run(): void
    {
        $invitado = User::create([
            'name'     => 'Invitado',
            'email'    => 'invitado@gmail.com',
            'password' => Hash::make('invitado123'),
        ]);
        $invitado->assignRole('invitado');

        $usuario = User::create([
            'name'     => 'Usuario',
            'email'    => 'usuario@gmail.com',
            'password' => Hash::make('usuario123'),
        ]);
        $usuario->assignRole('usuario');

        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole('administrador');
    }
}