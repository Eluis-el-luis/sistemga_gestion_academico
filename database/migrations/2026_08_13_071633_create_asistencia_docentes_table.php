<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docente')->onDelete('cascade');
            $table->foreignId('aula_asignatura_docente_id')->constrained('aula_asignatura_docente')->onDelete('cascade');
            $table->date('fecha');
            $table->boolean('presente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_docente');
    }
};
