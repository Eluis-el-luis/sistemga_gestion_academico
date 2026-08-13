<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_reparacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignatura')->onDelete('cascade');
            $table->decimal('nota_obtenida', 5, 2);
            $table->date('fecha');
            $table->enum('resultado', ['aprobado', 'reprobado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_reparacion');
    }
};
