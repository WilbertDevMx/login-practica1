<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'password_reset_tokens' de Laravel.
 *
 * Almacena los tokens de restablecimiento de contraseña
 * asociados a un correo electrónico.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'password_reset_tokens' con las columnas:
     * - email (string, clave primaria)
     * - token (string)
     * - created_at (timestamp, nullable)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'password_reset_tokens' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};