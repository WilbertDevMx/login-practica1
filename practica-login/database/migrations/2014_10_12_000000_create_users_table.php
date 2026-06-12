<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'users' de Laravel.
 *
 * Tabla base de usuarios del sistema con autenticación,
 * verificación de email, recordatorio de sesión y timestamps.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'users' con las columnas:
     * - id (autoincremental)
     * - name (string)
     * - email (string, único)
     * - email_verified_at (timestamp, nullable)
     * - password (string)
     * - remember_token (string, nullable, para "recordarme")
     * - timestamps (created_at, updated_at)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'users' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};