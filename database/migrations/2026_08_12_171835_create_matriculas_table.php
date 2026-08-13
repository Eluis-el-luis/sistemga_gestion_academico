<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aula')->onDelete('restrict');
            $table->foreignId('anio_escolar_id')->constrained('anio_escolar')->onDelete('restrict');
            $table->enum('estado', ['activo', 'retirado', 'repitente', 'promovido'])->default('activo');
            $table->date('fecha_matricula');
            $table->date('fecha_retiro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricula');
    }
};
