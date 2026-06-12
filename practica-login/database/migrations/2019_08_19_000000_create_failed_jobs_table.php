<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'failed_jobs' de Laravel.
 *
 * Esta tabla almacena los trabajos (jobs) fallidos en colas,
 * permitiendo su posterior revisión o reintento.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Crea la tabla 'failed_jobs' con las siguientes columnas:
     * - id (autoincremental)
     * - uuid (string único)
     * - connection (texto)
     * - queue (texto)
     * - payload (texto largo)
     * - exception (texto largo)
     * - failed_at (timestamp con valor por defecto CURRENT_TIMESTAMP)
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Revierte la migración.
     *
     * Elimina la tabla 'failed_jobs' si existe.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};