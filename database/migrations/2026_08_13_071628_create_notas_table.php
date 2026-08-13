<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('aula_asignatura_docente_id')->constrained('aula_asignatura_docente')->onDelete('cascade');
            $table->foreignId('corte_evaluativo_id')->constrained('corte_evaluativo')->onDelete('cascade');
            $table->decimal('nota_cuantitativa', 5, 2)->nullable(); 
            $table->foreignId('indicador_logro_id')->nullable()->constrained('indicador_logro')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota');
    }
};
