<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('two_factor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email'); // redundante pero útil para búsquedas
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action'); // 'setup', 'verify_attempt'
            $table->boolean('successful');
            $table->text('message')->nullable(); // detalle adicional (ej: "código inválido")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_logs');
    }
};
