<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aula', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 20);
            $table->foreignId('grado_id')->constrained('grado')->onDelete('restrict');
            $table->foreignId('modalidad_id')->constrained('modalidad')->onDelete('restrict');
            $table->string('turno', 20); // Ej. Matutino
            $table->foreignId('docente_guia_id')->constrained('docente')->onDelete('restrict');
            $table->foreignId('anio_escolar_id')->constrained('anio_escolar')->onDelete('restrict');
            $table->integer('cupo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aula');
    }
};
