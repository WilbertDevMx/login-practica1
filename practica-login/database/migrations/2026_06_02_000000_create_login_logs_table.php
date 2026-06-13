<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'login_logs'.
 *
 * Registra los intentos de inicio de sesión de los usuarios,
 * almacenando email, IP, resultado (éxito/fracaso) y user agent.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'login_logs' con las siguientes columnas:
     * - id (autoincremental)
     * - email (string)
     * - ip (string hasta 45 caracteres)
     * - exitoso (booleano)
     * - user_agent (string, nullable)
     * - timestamps (created_at, updated_at)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip', 45);
            $table->boolean('exitoso');
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'login_logs' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};