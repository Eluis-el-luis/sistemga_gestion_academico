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
        Schema::create('nota_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('actividad_evaluativa_id')->constrained('actividad_evaluativa')->onDelete('cascade');
            
            // Decimal 5,2 permite notas como 10.50 (por si el colegio usa decimales)
            $table->decimal('nota_obtenida', 5, 2); 
            
            $table->timestamps();

            // REGLA DE ORO: Un alumno solo puede tener UNA nota por cada actividad específica
            $table->unique(['matricula_id', 'actividad_evaluativa_id'], 'matricula_actividad_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_actividad');
    }
};
