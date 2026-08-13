<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuario')->onDelete('set null');
            $table->string('codigo_unico_persona', 20)->unique();
            $table->string('nombre_completo', 120);
            $table->enum('sexo', ['M', 'F']);
            $table->date('fecha_nacimiento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno');
    }
};
