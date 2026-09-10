<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_materia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('asignatura', function (Blueprint $table) {
            $table->foreignId('grupo_materia_id')->nullable()->after('area')->constrained('grupo_materia')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('asignatura', function (Blueprint $table) {
            $table->dropForeign(['grupo_materia_id']);
            $table->dropColumn('grupo_materia_id');
        });

        Schema::dropIfExists('grupo_materia');
    }
};