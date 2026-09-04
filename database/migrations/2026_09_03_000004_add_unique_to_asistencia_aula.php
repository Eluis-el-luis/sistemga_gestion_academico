<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_aula', function (Blueprint $table) {
            $table->unique(['matricula_id', 'fecha'], 'asistencia_aula_matricula_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_aula', function (Blueprint $table) {
            $table->dropUnique('asistencia_aula_matricula_fecha_unique');
        });
    }
};