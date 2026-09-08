<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota_actividad', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->after('nota_obtenida')->constrained('usuario')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('nota_actividad', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};