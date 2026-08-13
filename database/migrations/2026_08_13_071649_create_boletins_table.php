<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
            $table->foreignId('corte_evaluativo_id')->nullable()->constrained('corte_evaluativo')->onDelete('set null');
            $table->dateTime('fecha_generacion');
            $table->string('archivo_path', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletin');
    }
};
