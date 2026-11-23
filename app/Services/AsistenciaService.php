<?php

namespace App\Services;

class AsistenciaService
{
    public const ESTADOS_AULA = [
        'Presente',
        'Ausencia Injustificada',
        'Ausencia Justificada',
        'Retiro Anticipado',
        'Actividad Institucional',
    ];

    public const INCIDENCIAS_ASIGNATURA = [
        'Fuga',
        'Llegada Tardía',
        'Permiso de Salida',
    ];

    public const ESTADOS_PRESENTE = ['Presente', 'Actividad Institucional'];

    public const ESTADOS_JUSTIFICADO = ['Ausencia Justificada'];

    public function esPresente(?string $estado): bool
    {
        return in_array($estado, self::ESTADOS_PRESENTE, true);
    }

    public function esJustificado(?string $estado): bool
    {
        return in_array($estado, self::ESTADOS_JUSTIFICADO, true);
    }

    public function porcentaje(int $presentes, int $total): float
    {
        return $total > 0 ? round(($presentes / $total) * 100, 1) : 0.0;
    }
}