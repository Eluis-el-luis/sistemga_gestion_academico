<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anio_escolar', function (Blueprint $table) {
            // Agregar campo estado con los tres estados válidos
            $table->enum('estado', ['planificacion', 'activo', 'cerrado'])
                ->default('planificacion')
                ->after('fecha_fin')
                ->comment('Estado del año escolar: planificacion, activo, cerrado');

            // Campos para auditoría del proceso de cierre
            $table->timestamp('fecha_cierre')->nullable()->after('estado')
                ->comment('Fecha y hora en que se cerró el año escolar');
            $table->foreignId('cerrado_por')->nullable()->constrained('usuario')->onDelete('set null')->after('fecha_cierre')
                ->comment('Usuario que ejecutó el cierre del año escolar');
            $table->text('observaciones_cierre')->nullable()->after('cerrado_por')
                ->comment('Observaciones u observaciones del proceso de cierre');

            // Índice para consultas frecuentes por estado
            $table->index('estado', 'anio_escolar_estado_index');
        });

        // Migrar datos existentes: si activo=true -> estado=activo, sino planificacion
        // Usar sintaxis compatible con PostgreSQL (boolean) y SQLite/MySQL
        \DB::statement("UPDATE anio_escolar SET estado = CASE WHEN activo THEN 'activo' ELSE 'planificacion' END");

        // Eliminar el campo booleano activo (ya no se usa, reemplazado por estado)
        Schema::table('anio_escolar', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('anio_escolar', function (Blueprint $table) {
            // Restaurar campo activo
            $table->boolean('activo')->default(false)->after('fecha_fin');
        });

        // Migrar datos de vuelta
        \DB::statement("UPDATE anio_escolar SET activo = CASE WHEN estado = 'activo' THEN true ELSE false END");

        Schema::table('anio_escolar', function (Blueprint $table) {
            $table->dropColumn(['estado', 'fecha_cierre', 'cerrado_por', 'observaciones_cierre']);
            $table->dropIndex('anio_escolar_estado_index');
        });
    }
};