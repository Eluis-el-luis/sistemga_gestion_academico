<?php

namespace App\Services;

class AsistenciaService
{
    /**
     * Catálogo de estados de asistencia por aula (Pase de Lista del Guía).
     */
    public const ESTADOS_AULA = [
        'Presente',
        'Ausencia Injustificada',
        'Ausencia Justificada',
        'Retiro Anticipado',
        'Actividad Institucional',
    ];

    /**
     * Incidencias registrables por asignatura (excepciones a la presencia).
     */
    public const INCIDENCIAS_ASIGNATURA = [
        'Fuga',
        'Llegada Tardía',
        'Permiso de Salida',
    ];

    /**
     * Estados que cuentan como "presente" a efectos de estadística.
     */
    public const ESTADOS_PRESENTE = ['Presente', 'Actividad Institucional'];

    /**
     * Estados que cuentan como "ausencia justificada" a efectos de estadística.
     */
    public const ESTADOS_JUSTIFICADO = ['Ausencia Justificada'];

    /**
     * Indica si un estado de asistencia de aula cuenta como presencia.
     */
    public function esPresente(?string $estado): bool
    {
        return in_array($estado, self::ESTADOS_PRESENTE, true);
    }

    /**
     * Calcula el porcentaje de asistencia dados los contadores.
     */
    public function porcentaje(int $presentes, int $total): float
    {
        return $total > 0 ? round(($presentes / $total) * 100, 1) : 0.0;
    }
}