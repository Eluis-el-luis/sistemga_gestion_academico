<?php

namespace App\Services;

use App\Models\Nota;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\AulaAsignaturaDocente;

class NotaService
{
    /**
     * Registra/actualiza la nota final de un parcial para una matrícula,
     * escalando los puntos obtenidos a la escala 0-100 según el total posible
     * del corte (suma de puntajes máximos de sus actividades).
     */
    public function registrarNotaFinal(int $matriculaId, int $asignacionId, int $corteId, float $suma, ?float $totalPosible = null): ?Nota
    {
        $nota = $this->escalarNota($suma, $totalPosible);

        $codigo = $this->calcularIndicadorLogro((int) round($nota));
        $indicadorId = $codigo ? IndicadorLogro::where('codigo', $codigo)->value('id') : null;

        return Nota::updateOrCreate(
            [
                'matricula_id' => $matriculaId,
                'aula_asignatura_docente_id' => $asignacionId,
                'corte_evaluativo_id' => $corteId,
            ],
            [
                'nota_cuantitativa' => $nota,
                'indicador_logro_id' => $indicadorId,
            ]
        );
    }

    /**
     * Escala los puntos obtenidos a 0-100 según el total posible.
     * Si no se indica el total posible, asume que la suma ya está en escala 0-100.
     */
    public function escalarNota(float $puntos, ?float $totalPosible = null): float
    {
        if ($totalPosible && $totalPosible > 0) {
            $puntos = ($puntos / $totalPosible) * 100;
        }

        return max(0, min(100, round($puntos, 2)));
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

    /**
     * Resumen completo de calificaciones de un alumno en una asignación:
     * promedia los 4 cortes, calcula nota semestral, nota final, indicador y estado.
     */
    public function calcularResumenAsignatura(Matricula $matricula, AulaAsignaturaDocente $asignacion): array
    {
        $notas = Nota::where('matricula_id', $matricula->id)
            ->where('aula_asignatura_docente_id', $asignacion->id)
            ->get()
            ->keyBy('corte_evaluativo_id');

        // Cortes ordenados por número (1..4)
        $cortes = \App\Models\CorteEvaluativo::whereHas('anioEscolar', fn($q) => $q->where('activo', true))
            ->orderBy('numero')
            ->get();

        $valores = [];
        foreach ($cortes as $corte) {
            $valores[$corte->numero] = isset($notas[$corte->id])
                ? (int) round((float) $notas[$corte->id]->nota_cuantitativa)
                : null;
        }

        $semestre1 = $this->calcularNotaSemestral($valores[1] ?? null, $valores[2] ?? null);
        $semestre2 = $this->calcularNotaSemestral($valores[3] ?? null, $valores[4] ?? null);
        $notaFinal = $this->calcularNotaFinal(
            $valores[1] ?? null, $valores[2] ?? null, $valores[3] ?? null, $valores[4] ?? null
        );

        return [
            'cortes' => $valores,
            'semestre1' => $semestre1,
            'semestre2' => $semestre2,
            'promedio_general' => $this->calcularPromedioGeneral(array_values(array_filter($valores))),
            'nota_final' => $notaFinal,
            'indicador_final' => $notaFinal !== null ? $this->calcularIndicadorLogro($notaFinal) : null,
            'aprobado' => $notaFinal !== null ? $this->estaAprobado($notaFinal) : null,
        ];
    }
}