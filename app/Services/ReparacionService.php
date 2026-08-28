<?php

namespace App\Services;

use App\Models\ExamenReparacion;
use App\Models\Matricula;
use App\Models\Asignatura;

class ReparacionService
{
    /**
     * Nota mínima para aprobar (Aprendizaje Fundamental).
     */
    public const NOTA_APROBACION = 60;

    /**
     * Determina si una nota de reparación aprueba (>= 60) o reprueba (< 60).
     */
    public function evaluarResultado(float $notaObtenida): string
    {
        return $notaObtenida >= self::NOTA_APROBACION ? 'aprobado' : 'reprobado';
    }

    /**
     * Indica si un alumno debe presentar examen de reparación de una asignatura.
     * La regla: la nota final anual de la asignatura es menor a 60 y aún no tiene
     * un examen de reparación aprobado registrado.
     */
    public function debeReparar(Matricula $matricula, Asignatura $asignatura, ?int $notaFinalAnual): bool
    {
        if (is_null($notaFinalAnual)) {
            return false;
        }

        if ($notaFinalAnual >= self::NOTA_APROBACION) {
            return false;
        }

        $yaAproboReparacion = ExamenReparacion::where('matricula_id', $matricula->id)
            ->where('asignatura_id', $asignatura->id)
            ->where('resultado', 'aprobado')
            ->exists();

        return !$yaAproboReparacion;
    }

    /**
     * Registra el resultado de un examen de reparación aplicando la regla de aprobación.
     */
    public function registrar(Matricula $matricula, Asignatura $asignatura, float $notaObtenida, string $fecha): ExamenReparacion
    {
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
    }
}