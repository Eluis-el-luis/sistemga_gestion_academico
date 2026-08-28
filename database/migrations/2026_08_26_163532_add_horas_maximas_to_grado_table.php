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
        Schema::table('grado', function (Blueprint $table) {
            // Agregamos la columna con un valor por defecto de 35 para no romper datos existentes
            $table->integer('horas_maximas_semanales')->default(35)->after('modalidad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grado', function (Blueprint $table) {
            $table->dropColumn('horas_maximas_semanales');
        });
    }
};
