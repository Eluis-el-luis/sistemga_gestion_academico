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
        Schema::create('sustitucion_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_ausente_id')->constrained('docente')->onDelete('cascade');
            
            // Apunta al horario específico (día y bloque) que quedó vacío
            $table->foreignId('horario_id')->constrained('horario')->onDelete('cascade');
            
            // Puede ser nulo si el coordinador decreta "Estudio Independiente"
            $table->foreignId('docente_sustito_id')->nullable()->constrained('docente')->onDelete('set null');
            
            $table->date('fecha_ausencia');
            $table->enum('estado', ['Pendiente', 'Asignada', 'Estudio Independiente', 'Completada'])->default('Pendiente');
            
            $table->text('indicaciones_clase')->nullable(); // Ej: "Resolver páginas 45-50 del libro"
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sustitucion_docente');
    }
};
