<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function down(): void
    {
        Schema::dropIfExists('asistencia_asignatura');
    }
    
    public function up(): void
    {
        Schema::create('asistencia_asignatura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignatura')->onDelete('cascade');
            
            // Conectamos con la tabla de bloques horarios que usa Subdirección
            $table->foreignId('bloque_horario_id')->constrained('bloque_horario')->onDelete('cascade');
            
            $table->date('fecha');
            
            // Catálogo: Fuga, Llegada Tardía, Permiso, Presente
            $table->string('estado_incidencia', 50); 
            $table->text('observacion')->nullable(); // Para que el maestro anote "Se fue a enfermería"
            
            $table->timestamps();

            // Evitar que el mismo maestro reporte dos veces la misma incidencia en el mismo bloque exacto
            $table->unique(['matricula_id', 'asignatura_id', 'bloque_horario_id', 'fecha'], 'asistencia_asig_unica');
        });
    }

    /**
     * Reverse the migrations.
     */
    
};
