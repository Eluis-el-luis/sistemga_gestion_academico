<?php

namespace App\Services;

use App\Models\Aula;
use App\Models\MallaCurricular;
use App\Models\AulaAsignaturaDocente;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;

class AulaService
{
    /**
     * Crea un aula y le asigna automáticamente las materias de la malla curricular.
     */
    public function crearAulaConMalla(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            // 1. Creamos el registro base del aula
            $aula = Aula::create($datos);

            // 2. Sincronizamos las asignaturas desde la malla
            $this->sincronizarMalla($aula);

            return $aula;
        });
    }

    /**
     * Reemplaza las asignaturas de un aula con las de su malla curricular.
     * (Usa 'activo' = true únicamente).
     */
    public function sincronizarMalla(Aula $aula): void
    {
        // Elimina las asignaturas actuales del aula
        AulaAsignaturaDocente::where('aula_id', $aula->id)->delete();

        // Carga las materias activas de la malla del grado
        $materiasPlantilla = MallaCurricular::where('grado_id', $aula->grado_id)
                                            ->where('activo', true)
                                            ->get();

        $asignaciones = [];
        foreach ($materiasPlantilla as $materia) {
            $asignaciones[] = [
                'aula_id' => $aula->id,
                'asignatura_id' => $materia->asignatura_id,
                'docente_id' => null,
                'anio_escolar_id' => $aula->anio_escolar_id,
                'horas_semanales' => $materia->horas_semanales_sugeridas ?? 0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($asignaciones)) {
            AulaAsignaturaDocente::insert($asignaciones);
        }
    }

    /**
     * Elimina un aula y todas sus relaciones (matrículas, asignaturas y horarios)
     * dentro de una transacción, para evitar bloqueos por restricción de llave foránea.
     */
    public function eliminarAula(Aula $aula): void
    {
        DB::transaction(function () use ($aula) {
            // 1. Matrículas del aula (tienen FK a aula)
            Matricula::where('aula_id', $aula->id)->forceDelete();

            // 2. Asignaciones aula-asignatura (borran horarios en cascada vía FK)
            AulaAsignaturaDocente::where('aula_id', $aula->id)->delete();

            // 3. Finalmente, el aula
            $aula->delete();
        });
    }
}