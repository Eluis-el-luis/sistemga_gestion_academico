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
        Schema::table('bloque_horario', function (Blueprint $table) {
            // numero_bloque: Identifica si es la 1ra Hora, 2da Hora, Receso 1, etc.
            $table->integer('numero_bloque')->after('turno')->nullable();
            
            // tipo_jornada: Diferencia entre el horario normal, viernes o días especiales
            $table->string('tipo_jornada')->default('Regular')->after('numero_bloque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bloque_horario', function (Blueprint $table) {
            $table->dropColumn(['numero_bloque', 'tipo_jornada']);
        });
    }
};
