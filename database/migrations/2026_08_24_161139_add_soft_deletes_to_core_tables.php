<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregamos la columna deleted_at a las tablas núcleo
        Schema::table('usuario', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('alumno', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('docente', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('matricula', function (Blueprint $table) { $table->softDeletes(); });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('alumno', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('docente', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('matricula', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};