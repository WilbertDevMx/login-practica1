<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla de sesiones de Laravel.
 *
 * Esta tabla es utilizada por el framework para almacenar las sesiones de usuario,
 * ya sea con driver 'database'. Los campos son los estándar de Laravel.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'sessions' con los siguientes campos:
     * - id (string, clave primaria)
     * - user_id (clave foránea nullable con índice)
     * - ip_address (string hasta 45 chars, nullable)
     * - user_agent (texto, nullable)
     * - payload (texto largo)
     * - last_activity (entero con índice)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'sessions' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};