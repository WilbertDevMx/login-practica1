<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'two_factor_logs'.
 *
 * Registra eventos relacionados con la autenticación de dos factores (2FA):
 * - Configuración (setup)
 * - Intentos de verificación (verify_attempt)
 * - Éxito o fracaso con mensajes adicionales.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'two_factor_logs' con los campos:
     * - id (autoincremental)
     * - user_id (clave foránea a users, cascade)
     * - email (string, redundante para búsquedas)
     * - ip (string hasta 45 caracteres, nullable)
     * - user_agent (texto, nullable)
     * - action (string: 'setup', 'verify_attempt')
     * - successful (booleano)
     * - message (texto, nullable, detalle adicional)
     * - timestamps (created_at, updated_at)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('two_factor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action');
            $table->boolean('successful');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'two_factor_logs' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_logs');
    }
};