<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloque_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modalidad_id')->constrained('modalidad')->cascadeOnDelete();
            $table->string('turno', 20); // Matutino, Vespertino
            $table->string('nombre', 50); // Ej: "1ra Hora", "Recreo", "2da Hora"
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('es_recreo')->default(false); // Para saber si es clase o descanso
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloque_horarios');
    }
};
