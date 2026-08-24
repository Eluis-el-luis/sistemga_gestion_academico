<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aula_asignatura_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aula')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignatura')->onDelete('restrict');
            $table->foreignId('docente_id')->nullable()->constrained('docente')->nullOnDelete();
            $table->foreignId('anio_escolar_id')->constrained('anio_escolar')->onDelete('restrict');
            $table->decimal('horas_semanales', 4, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aula_asignatura_docente');
    }
};
