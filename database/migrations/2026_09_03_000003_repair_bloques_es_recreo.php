<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repara los bloques de horario que quedaron marcados como "receso"
     * por un bug anterior (todos guardados con es_recreo = true).
     *
     * Regla: un bloque es recreo únicamente si su nombre hace referencia
     * explícita a receso/merienda. El resto (1ra hora, devocional, etc.) son
     * clases regulares y deben tener es_recreo = false.
     */
    public function up(): void
    {
        // 1. Recreos (se mantienen/actualizan a true)
        DB::table('bloque_horario')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%recreo%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%receso%'])
                  ->orWhereRaw('LOWER(nombre) LIKE ?', ['%merienda%']);
            })
            ->update(['es_recreo' => true]);

        // 2. Clases regulares (todo lo demás pasa a false)
        DB::table('bloque_horario')
            ->where(function ($q) {
                $q->whereRaw('LOWER(nombre) NOT LIKE ?', ['%recreo%'])
                  ->whereRaw('LOWER(nombre) NOT LIKE ?', ['%receso%'])
                  ->whereRaw('LOWER(nombre) NOT LIKE ?', ['%merienda%']);
            })
            ->update(['es_recreo' => false]);
    }

    public function down(): void
    {
        // Sin reversión: es una corrección de datos.
    }
};