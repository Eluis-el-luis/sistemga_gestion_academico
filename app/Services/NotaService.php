<?php

namespace App\Services;

use App\Models\Nota;
use App\Models\IndicadorLogro;

class NotaService
{
    /**
     * Registra/actualiza la nota final de un parcial para una matrícula,
     * calculando el indicador de logro cualitativo a partir de la suma cuantitativa.
     * Centraliza la auto-suma usada por NotaController y el recálculo de actividades.
     */
    public function registrarNotaFinal(int $matriculaId, int $asignacionId, int $corteId, float $suma): ?Nota
    {
        $suma = max(0, min(100, round($suma, 2)));

        $codigo = $this->calcularIndicadorLogro((int) round($suma));
        $indicadorId = $codigo ? IndicadorLogro::where('codigo', $codigo)->value('id') : null;

        return Nota::updateOrCreate(
            [
                'matricula_id' => $matriculaId,
                'aula_asignatura_docente_id' => $asignacionId,
                'corte_evaluativo_id' => $corteId,
            ],
            [
                'nota_cuantitativa' => $suma,
                'indicador_logro_id' => $indicadorId,
            ]
        );
    }

    /**
     * Convierte la nota cuantitativa (0-100) en el Indicador de Logro Cualitativo (MINED)
     * Basado en los rangos oficiales del MINED.
     */
    public function calcularIndicadorLogro(?int $nota): ?string
    {
        if (is_null($nota)) {
            return null;
        }

        if ($nota >= 90 && $nota <= 100) {
            return 'AA'; // Aprendizaje Avanzado
        } elseif ($nota >= 76 && $nota <= 89) {
            return 'AS'; // Aprendizaje Satisfactorio
        } elseif ($nota >= 60 && $nota <= 75) {
            return 'AF'; // Aprendizaje Fundamental
        } elseif ($nota >= 0 && $nota <= 59) {
            return 'AI'; // Aprendizaje Inicial
        }

        // Podrías lanzar una excepción aquí si la nota es mayor a 100 o menor a 0
        throw new \InvalidArgumentException("La nota debe estar entre 0 y 100. Valor recibido: {$nota}");
    }

    /**
     * Calcula la Nota del Semestre (Promedio de 2 cortes evaluativos)
     * Aplica el redondeo estándar (ej. 89.5 sube a 90).
     */
    public function calcularNotaSemestral(?int $corte1, ?int $corte2): ?int
    {
        if (is_null($corte1) || is_null($corte2)) {
            return null; // El semestre no se puede calcular si falta un corte
        }

        return (int) round(($corte1 + $corte2) / 2);
    }

    /**
     * Calcula la Nota Final (NF) anual de una asignatura
     * Promedia los 4 cortes evaluativos (IP, IIP, IIIP, IVP).
     */
    public function calcularNotaFinal(?int $corte1, ?int $corte2, ?int $corte3, ?int $corte4): ?int
    {
        // Se necesitan los 4 cortes para la nota final anual
        if (is_null($corte1) || is_null($corte2) || is_null($corte3) || is_null($corte4)) {
            return null;
        }

        return (int) round(($corte1 + $corte2 + $corte3 + $corte4) / 4);
    }

    /**
     * Calcula el Promedio General Acumulado de un estudiante en un corte o al final del año.
     * Retorna con 2 decimales para coincidir con las sábanas del MINED (Ej: 86.55).
     */
    public function calcularPromedioGeneral(array $notas): float
    {
        // Filtramos para ignorar materias que aún no tengan nota ingresada
        $notasValidas = array_filter($notas, function ($nota) {
            return !is_null($nota) && is_numeric($nota);
        });

        if (count($notasValidas) === 0) {
            return 0.00;
        }

        $suma = array_sum($notasValidas);
        $promedio = $suma / count($notasValidas);

        // Redondeamos a 2 decimales exactos, tal como se refleja en la columna PROMEDIO
        return round($promedio, 2); 
    }
    
    /**
     * Verifica si un estudiante deja una clase (Aprobado o Reprobado)
     * Retorna true si aprueba, false si reprueba (aplazado).
     */
    public function estaAprobado(int $notaFinal): bool
    {
        return $notaFinal >= 60; // 60 es la nota mínima para aprobar (Aprendizaje Fundamental)
    }
}