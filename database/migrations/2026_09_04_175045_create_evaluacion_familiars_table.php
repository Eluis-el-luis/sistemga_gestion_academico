<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('evaluacion_familiar', function (Blueprint $table) {
        $table->id();
        $table->foreignId('matricula_id')->constrained('matricula')->onDelete('cascade');
        $table->foreignId('corte_evaluativo_id')->constrained('corte_evaluativo')->onDelete('cascade');
        $table->string('evaluacion', 2); // Guardará 'B', 'MB' o 'EX'
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_familiars');
    }
};
