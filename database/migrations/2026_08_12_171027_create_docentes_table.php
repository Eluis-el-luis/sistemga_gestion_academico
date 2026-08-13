<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('usuario')->onDelete('cascade');
            $table->string('codigo_unico_persona', 20)->unique();
            $table->enum('sexo', ['M', 'F']);
            $table->boolean('es_coordinador')->default(false);
            $table->foreignId('modalidad_coordina_id')->nullable()->constrained('modalidad')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};
