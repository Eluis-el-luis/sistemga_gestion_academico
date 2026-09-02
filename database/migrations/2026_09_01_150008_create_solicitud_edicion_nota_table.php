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
        Schema::create('solicitud_edicion_nota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docente')->onDelete('cascade');
            
            // Apuntamos al registro de la nota final del parcial que está bloqueado
            $table->foreignId('nota_id')->constrained('nota')->onDelete('cascade'); 
            
            $table->text('motivo');
            $table->enum('estado', ['Pendiente', 'Aprobada', 'Rechazada'])->default('Pendiente');
            
            // Quién aprobó o rechazó (Directora / Subdirectora)
            $table->foreignId('autorizado_por')->nullable()->constrained('usuario')->onDelete('set null');
            
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_edicion_nota');
    }
};
