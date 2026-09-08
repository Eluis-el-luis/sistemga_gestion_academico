<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->unique(
                ['matricula_id', 'aula_asignatura_docente_id', 'corte_evaluativo_id'],
                'nota_matricula_asignacion_corte_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->dropUnique('nota_matricula_asignacion_corte_unique');
        });
    }
};