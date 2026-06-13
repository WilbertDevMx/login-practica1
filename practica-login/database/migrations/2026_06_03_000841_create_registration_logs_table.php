<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'registration_logs'.
 *
 * Registra cada intento de registro de usuario en el sistema.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'registration_logs' con los campos:
     * - id (autoincremental)
     * - user_id (clave foránea a users, nullable, set null al borrar)
     * - email (string)
     * - ip (dirección IP, nullable)
     * - user_agent (texto, nullable)
     * - successful (booleano)
     * - message (texto, nullable)
     * - timestamps (created_at, updated_at)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('registration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email');
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('successful');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'registration_logs' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_logs');
    }
};