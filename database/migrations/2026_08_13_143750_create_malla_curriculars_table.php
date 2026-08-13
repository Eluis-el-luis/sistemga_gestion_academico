<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('malla_curricular', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id')->constrained('grado')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignatura')->onDelete('cascade');
            $table->decimal('horas_semanales_sugeridas', 4, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('malla_curricular');
    }
};
