<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal de la aplicación.
 *
 * Llama a los seeders específicos para poblar las tablas
 * con datos iniciales o de prueba.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la base de datos.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UsersTableSeeder::class,
        ]);
    }
}