<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            // Datos Domiciliares y Médicos
            $table->text('direccion_domiciliar')->nullable();
            $table->text('enfermedades_cronicas')->nullable();
            
            // Relaciones en el colegio
            $table->string('hermanos_en_colegio')->nullable();

            // Datos de la Madre
            $table->string('madre_nombre_completo')->nullable();
            $table->string('madre_cedula', 20)->nullable();
            $table->string('madre_telefono', 20)->nullable();
            $table->string('madre_ocupacion')->nullable();
            $table->boolean('madre_asiste_iglesia')->default(false);
            $table->string('madre_nombre_iglesia')->nullable();

            // Datos del Padre
            $table->string('padre_nombre_completo')->nullable();
            $table->string('padre_cedula', 20)->nullable();
            $table->string('padre_telefono', 20)->nullable();
            $table->string('padre_ocupacion')->nullable();
            $table->boolean('padre_asiste_iglesia')->default(false);
            $table->string('padre_nombre_iglesia')->nullable();

            // Datos del Tutor
            $table->string('tutor_nombre_completo')->nullable();
            $table->string('tutor_cedula', 20)->nullable();
            $table->string('tutor_telefono', 20)->nullable();
            $table->string('tutor_ocupacion')->nullable();

            // Persona autorizada para retirar
            $table->string('autorizado_retirar_nombre')->nullable();
            $table->string('autorizado_retirar_cedula', 20)->nullable();
            $table->string('autorizado_retirar_telefono', 20)->nullable();

            // Compromiso institucional
            $table->boolean('acepta_compromiso_cristiano')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->dropColumn([
                'direccion_domiciliar', 'enfermedades_cronicas', 'hermanos_en_colegio',
                'madre_nombre_completo', 'madre_cedula', 'madre_telefono', 'madre_ocupacion', 'madre_asiste_iglesia', 'madre_nombre_iglesia',
                'padre_nombre_completo', 'padre_cedula', 'padre_telefono', 'padre_ocupacion', 'padre_asiste_iglesia', 'padre_nombre_iglesia',
                'tutor_nombre_completo', 'tutor_cedula', 'tutor_telefono', 'tutor_ocupacion',
                'autorizado_retirar_nombre', 'autorizado_retirar_cedula', 'autorizado_retirar_telefono',
                'acepta_compromiso_cristiano'
            ]);
        });
    }
};