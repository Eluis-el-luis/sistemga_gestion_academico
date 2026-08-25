<?php

namespace App\Services;

use App\Models\Aula;
use App\Models\MallaCurricular;
use App\Models\AulaAsignaturaDocente;
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

            // 2. Buscamos la plantilla de materias para este grado
            // Obtenemos solo las materias que están activas en la Malla Oficial
            $materiasPlantilla = MallaCurricular::where('grado_id', $aula->grado_id)
                                                ->where('activo', true)
                                                ->get();

            // 3. Copiamos cada materia de la plantilla a esta aula específica
            $asignaciones = [];
            foreach ($materiasPlantilla as $materia) {
                $asignaciones[] = [
                    'aula_id' => $aula->id,
                    'asignatura_id' => $materia->asignatura_id,
                    'docente_id' => null, // Queda en blanco hasta que coordinación asigne al profesor
                    'anio_escolar_id' => $aula->anio_escolar_id,
                    'horas_semanales' => $materia->horas_semanales_sugeridas ?? 0,
                    //'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insertamos todas las materias de golpe por rendimiento
            if (!empty($asignaciones)) {
                AulaAsignaturaDocente::insert($asignaciones);
            }

            return $aula;
        });
    }
}