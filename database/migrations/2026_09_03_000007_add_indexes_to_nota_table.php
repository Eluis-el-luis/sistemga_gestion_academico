<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->index('matricula_id', 'nota_matricula_id_index');
            $table->index('aula_asignatura_docente_id', 'nota_asignacion_id_index');
            $table->index('corte_evaluativo_id', 'nota_corte_id_index');
            $table->index(
                ['aula_asignatura_docente_id', 'corte_evaluativo_id'],
                'nota_asignacion_corte_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->dropIndex('nota_matricula_id_index');
            $table->dropIndex('nota_asignacion_id_index');
            $table->dropIndex('nota_corte_id_index');
            $table->dropIndex('nota_asignacion_corte_index');
        });
    }
};