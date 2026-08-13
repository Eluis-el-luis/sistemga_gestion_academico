<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avance_contenido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_asignatura_docente_id')->constrained('aula_asignatura_docente')->onDelete('cascade');
            $table->string('mes', 7); // Ej. "2026-08"
            $table->decimal('porcentaje_avance', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avance_contenido');
    }
};
