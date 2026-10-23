<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Escala MINED 2026: el indicador de la banda 60-75 pasa de
     * 'AF' (Aprendizaje Fundamental) a 'AE' (Aprendizaje Esperado).
     * Las notas referencian indicador_logro por FK, así que basta
     * actualizar el catálogo (se conserva el id).
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('indicador_logro')
            ->where('codigo', 'AF')
            ->update(['codigo' => 'AE', 'nombre' => 'Aprendizaje Esperado']);
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('indicador_logro')
            ->where('codigo', 'AE')
            ->update(['codigo' => 'AF', 'nombre' => 'Aprendizaje Fundamental']);
    }
};
