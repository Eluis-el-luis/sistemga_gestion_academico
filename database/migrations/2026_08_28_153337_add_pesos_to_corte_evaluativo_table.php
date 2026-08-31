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
        Schema::table('corte_evaluativo', function (Blueprint $table) {
            $table->integer('peso_acumulado')->default(60)->after('fecha_fin');
            $table->integer('peso_examen')->default(40)->after('peso_acumulado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_evaluativo', function (Blueprint $table) {
            $table->dropColumn(['peso_acumulado', 'peso_examen']);
        });
    }
};
