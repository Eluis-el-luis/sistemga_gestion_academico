<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normaliza los días de semana existentes: "Miercoles" -> "Miércoles"
        DB::table('horario')
            ->where('dia_semana', 'Miercoles')
            ->update(['dia_semana' => 'Miércoles']);
    }

    public function down(): void
    {
        DB::table('horario')
            ->where('dia_semana', 'Miércoles')
            ->update(['dia_semana' => 'Miercoles']);
    }
};