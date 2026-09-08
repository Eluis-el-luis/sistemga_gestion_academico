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
        Schema::create('incidencia_disciplinaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('docente_reporta_id')->constrained('docente')->onDelete('cascade');
            $table->foreignId('coordinador_atiende_id')->nullable()->constrained('usuario')->onDelete('set null');
            
            $table->enum('nivel_falta', ['Leve', 'Grave', 'Muy Grave']);
            $table->text('descripcion');
            $table->enum('estado', ['Reportada', 'En Revisión', 'Citación a Padres', 'Cerrada'])->default('Reportada');
            
            $table->date('fecha_incidencia');
            $table->dateTime('fecha_citacion_padres')->nullable();
            $table->text('resolucion_final')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencia_disciplinaria');
    }
};
