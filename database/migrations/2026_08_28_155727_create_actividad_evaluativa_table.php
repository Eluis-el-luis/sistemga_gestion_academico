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
        Schema::create('actividad_evaluativa', function (Blueprint $table) {
            $table->id();
            
            // Llaves foráneas exactas que pide tu controlador
            $table->foreignId('aula_asignatura_docente_id')->constrained('aula_asignatura_docente')->onDelete('cascade');
            $table->foreignId('corte_evaluativo_id')->constrained('corte_evaluativo')->onDelete('cascade');
            
            // Datos de la actividad
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('puntaje_maximo');
            $table->date('fecha')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_evaluativa');
    }
};
