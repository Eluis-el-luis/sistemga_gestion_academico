<?php

namespace App\Services;

use App\Models\Asignatura;
use App\Models\ExamenReparacion;
use App\Models\Matricula;
use Illuminate\Support\Facades\Log;

class ReparacionService
{
    public const NOTA_APROBACION = 60;

    public function evaluarResultado(float $notaObtenida): string
    {
        return $notaObtenida >= self::NOTA_APROBACION ? 'aprobado' : 'reprobado';
    }

    public function debeReparar(Matricula $matricula, Asignatura $asignatura, ?int $notaFinalAnual): bool
    {
        if (is_null($notaFinalAnual) || $notaFinalAnual >= self::NOTA_APROBACION) {
            return false;
        }

        return !ExamenReparacion::where('matricula_id', $matricula->id)
            ->where('asignatura_id', $asignatura->id)
            ->where('resultado', 'aprobado')
            ->exists();
    }

    public function registrar(Matricula $matricula, Asignatura $asignatura, float $notaObtenida, string $fecha): ExamenReparacion
    {
        try {
            $resultado = $this->evaluarResultado($notaObtenida);

            return ExamenReparacion::updateOrCreate(
                [
                    'matricula_id' => $matricula->id,
                    'asignatura_id' => $asignatura->id,
                ],
                [
                    'nota_obtenida' => $notaObtenida,
                    'fecha' => $fecha,
                    'resultado' => $resultado,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Error al registrar examen de reparación', [
                'matricula_id' => $matricula->id,
                'asignatura_id' => $asignatura->id,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible registrar el examen de reparación.', 0, $e);
        }
    }
}