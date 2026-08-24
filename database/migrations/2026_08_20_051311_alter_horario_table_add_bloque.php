<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            // Eliminamos las horas sueltas
            $table->dropColumn(['hora_inicio', 'hora_fin']);
            // Agregamos la llave foránea al bloque oficial
            $table->foreignId('bloque_horario_id')->after('dia_semana')->constrained('bloque_horario')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('horario', function (Blueprint $table) {
            $table->dropForeign(['bloque_horario_id']);
            $table->dropColumn('bloque_horario_id');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
        });
    }
};
