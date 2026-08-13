<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoyo_padres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aula')->onDelete('cascade');
            $table->string('mes', 7);
            $table->integer('cantidad_apoyan');
            $table->integer('total_padres');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apoyo_padre');
    }
};
