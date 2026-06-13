<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para agregar campo de Google 2FA a la tabla de usuarios.
 *
 * Añade la columna 'google2fa_secret' que almacena la clave secreta
 * para la autenticación de dos factores con Google Authenticator.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Agrega la columna 'google2fa_secret' (texto, nullable) a la tabla 'users'.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google2fa_secret')->nullable();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la columna 'google2fa_secret' de la tabla 'users'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
        });
    }
};