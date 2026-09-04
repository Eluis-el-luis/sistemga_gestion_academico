<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Elimina la tabla huérfana 'asistencia_docente' (fue reemplazada por 'asistencia_personal').
        Schema::dropIfExists('asistencia_docente');

        // 2. Agrega contexto de turno a la asistencia personal del docente.
        Schema::table('asistencia_personal', function (Blueprint $table) {
            $table->string('turno', 20)->nullable()->after('hora_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_personal', function (Blueprint $table) {
            $table->dropColumn('turno');
        });

        // No se recrea 'asistencia_docente' porque la migración original fue eliminada.
    }
};