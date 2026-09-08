<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('envio_boletines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aula')->onDelete('cascade');
            $table->foreignId('corte_evaluativo_id')->constrained('corte_evaluativo')->onDelete('cascade');
            
            // Quién autoriza y cuándo
            $table->foreignId('docente_guia_id')->constrained('docente');
            
            // Control de flujo para el Gestor
            $table->string('estado')->default('Autorizado'); // Autorizado, Impreso, Devuelto
            
            $table->timestamps();

            // Evitar que un maestro envíe la misma aula dos veces para el mismo parcial
            $table->unique(['aula_id', 'corte_evaluativo_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('envio_boletines');
    }
};
