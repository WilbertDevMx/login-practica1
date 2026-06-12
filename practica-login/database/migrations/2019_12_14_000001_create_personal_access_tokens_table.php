<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'personal_access_tokens' de Laravel Sanctum.
 *
 * Esta tabla almacena los tokens de acceso personal (API tokens)
 * generados por Sanctum para autenticación de usuarios y dispositivos.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'personal_access_tokens' con las columnas:
     * - id (autoincremental)
     * - tokenable (morphs: tokenable_id y tokenable_type)
     * - name (string, nombre del token)
     * - token (string de 64 chars, único)
     * - abilities (texto, nullable, capacidades del token)
     * - last_used_at (timestamp, nullable)
     * - expires_at (timestamp, nullable)
     * - timestamps (created_at, updated_at)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'personal_access_tokens' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};