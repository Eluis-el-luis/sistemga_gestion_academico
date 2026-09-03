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
        Schema::table('actividad_evaluativa', function (Blueprint $table) {
            // Añadimos el tipo para diferenciar acumulados de exámenes
            $table->string('tipo')->default('acumulado')->after('descripcion'); 
        });
    }

    public function down(): void
    {
        Schema::table('actividad_evaluativa', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
