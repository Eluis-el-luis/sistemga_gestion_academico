<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_aula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->date('fecha');
            $table->boolean('presente');
            $table->boolean('justificada')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_aula');
    }
};
