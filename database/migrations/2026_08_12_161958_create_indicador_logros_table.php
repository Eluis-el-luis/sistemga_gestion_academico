<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('indicador_logro', function (Blueprint $table) {
        $table->id();
        $table->string('codigo', 2);
        $table->string('nombre', 40);
        $table->decimal('nota_min', 5, 2)->nullable();
        $table->decimal('nota_max', 5, 2)->nullable();
        $table->foreignId('modalidad_id')->constrained('modalidad')->onDelete('cascade');
        $table->foreignId('grado_min')->nullable()->constrained('grado')->onDelete('set null');
        $table->foreignId('grado_max')->nullable()->constrained('grado')->onDelete('set null');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicador_logro');
    }
};
