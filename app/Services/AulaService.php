<?php

namespace App\Services;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\MallaCurricular;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AulaService
{
    public function crearAulaConMalla(array $datos): Aula
    {
        try {
            return DB::transaction(function () use ($datos) {
                $aula = Aula::create($datos);
                $this->sincronizarMalla($aula);

                return $aula;
            });
        } catch (\Throwable $e) {
            Log::error('Error al crear aula con malla curricular', [
                'datos' => $datos,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible crear el aula y sincronizar su malla curricular.', 0, $e);
        }
    }

    public function sincronizarMalla(Aula $aula): void
    {
        try {
            AulaAsignaturaDocente::where('aula_id', $aula->id)->delete();

            $materiasPlantilla = MallaCurricular::where('grado_id', $aula->grado_id)
                ->where('activo', true)
                ->get();

            $asignaciones = [];
            $now = now();

            foreach ($materiasPlantilla as $materia) {
                $asignaciones[] = [
                    'aula_id' => $aula->id,
                    'asignatura_id' => $materia->asignatura_id,
                    'docente_id' => null,
                    'anio_escolar_id' => $aula->anio_escolar_id,
                    'horas_semanales' => $materia->horas_semanales_sugeridas ?? 0,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($asignaciones !== []) {
                AulaAsignaturaDocente::insert($asignaciones);
            }
        } catch (\Throwable $e) {
            Log::error('Error al sincronizar malla curricular en aula', [
                'aula_id' => $aula->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible sincronizar la malla curricular del aula.', 0, $e);
        }
    }

    public function eliminarAula(Aula $aula): void
    {
        try {
            DB::transaction(function () use ($aula) {
                Matricula::where('aula_id', $aula->id)->forceDelete();
                AulaAsignaturaDocente::where('aula_id', $aula->id)->delete();
                $aula->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Error al eliminar aula', [
                'aula_id' => $aula->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible eliminar el aula.', 0, $e);
        }
    }
}