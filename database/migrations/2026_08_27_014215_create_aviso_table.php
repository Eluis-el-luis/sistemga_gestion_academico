<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aviso', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 120);
            $table->text('mensaje');
            $table->foreignId('autor_id')->constrained('usuario')->onDelete('cascade');
            $table->boolean('activo')->default(true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aviso');
    }
};
