<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('usuario')->onDelete('set null')->after('indicador_logro_id');
        });
    }

    public function down(): void
    {
        Schema::table('nota', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};