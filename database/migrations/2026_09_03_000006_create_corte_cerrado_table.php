<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corte_cerrado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_asignatura_docente_id')->constrained('aula_asignatura_docente')->onDelete('cascade');
            $table->foreignId('corte_evaluativo_id')->constrained('corte_evaluativo')->onDelete('cascade');
            $table->boolean('bloqueado')->default(true);
            $table->foreignId('cerrado_por')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();

            // Un parcial + asignación solo puede cerrarse una vez
            $table->unique(
                ['aula_asignatura_docente_id', 'corte_evaluativo_id'],
                'corte_cerrado_asignacion_corte_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corte_cerrado');
    }
};