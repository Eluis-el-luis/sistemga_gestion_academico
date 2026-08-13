<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corte_evaluativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolar')->onDelete('cascade');
            $table->tinyInteger('numero');
            $table->tinyInteger('semestre');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_evaluativo');
    }
};
