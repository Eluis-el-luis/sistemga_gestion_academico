<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencia_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_entrada')->nullable();
            // Estados posibles para manejar eventualidades
            $table->enum('estado', ['Presente', 'Retardo', 'Ausente', 'Justificado'])->default('Presente');
            $table->string('observaciones')->nullable(); // Ej: "Constancia médica del MINSA"
            $table->timestamps();

            // Un empleado solo puede marcar una entrada por día
            $table->unique(['usuario_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_personals');
    }
};
