<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anio_escolar', function (Blueprint $table) {
            $table->id(); 
            $table->string('nombre', 9); 
            $table->date('fecha_inicio'); 
            $table->date('fecha_fin'); 
            $table->boolean('activo')->default(false); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anio_escolar');
    }
};
