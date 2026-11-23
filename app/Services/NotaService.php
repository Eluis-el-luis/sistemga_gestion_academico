<?php

namespace App\Services;

use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\Nota;

class NotaService
{
    public function registrarNotaFinal(
        int $matriculaId,
        int $asignacionId,
        int $corteId,
        float $suma,
        ?float $totalPosible = null,
        ?int $updatedBy = null
    ): ?Nota {
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
                'updated_by' => $updatedBy,
            ]
        );
    }

    public function escalarNota(float $puntos, ?float $totalPosible = null): float
    {
        if ($totalPosible && $totalPosible > 0) {
            $puntos = ($puntos / $totalPosible) * 100;
        }

        return max(0, min(100, round($puntos, 2)));
    }

    public function calcularIndicadorLogro(?int $nota): ?string
    {
        if (is_null($nota)) {
            return null;
        }

        if ($nota >= 90 && $nota <= 100) {
            return 'AA';
        }
        if ($nota >= 76 && $nota <= 89) {
            return 'AS';
        }
        if ($nota >= 60 && $nota <= 75) {
            return 'AE';
        }
        if ($nota >= 0 && $nota <= 59) {
            return 'AI';
        }

        throw new \InvalidArgumentException("La nota debe estar entre 0 y 100. Valor recibido: {$nota}");
    }

    public function calcularNotaSemestral(?int $corte1, ?int $corte2): ?int
    {
        if (is_null($corte1) || is_null($corte2)) {
            return null;
        }

        return (int) round(($corte1 + $corte2) / 2);
    }

    public function calcularNotaFinal(?int $corte1, ?int $corte2, ?int $corte3, ?int $corte4): ?int
    {
        if (is_null($corte1) || is_null($corte2) || is_null($corte3) || is_null($corte4)) {
            return null;
        }

        return (int) round(($corte1 + $corte2 + $corte3 + $corte4) / 4);
    }

    public function calcularPromedioGeneral(array $notas): float
    {
        $notasValidas = array_filter($notas, fn ($nota) => !is_null($nota) && is_numeric($nota));

        if ($notasValidas === []) {
            return 0.00;
        }

        return round(array_sum($notasValidas) / count($notasValidas), 2);
    }

    public function estaAprobado(int $notaFinal): bool
    {
        return $notaFinal >= 60;
    }

    public function calcularResumenAsignatura(Matricula $matricula, AulaAsignaturaDocente $asignacion): array
    {
        $notas = Nota::with('indicadorLogro')
            ->where('matricula_id', $matricula->id)
            ->where('aula_asignatura_docente_id', $asignacion->id)
            ->get()
            ->keyBy('corte_evaluativo_id');

        $cortes = CorteEvaluativo::whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
            ->orderBy('numero')
            ->get();

        $valores = [];
        $indicadores = [];

        foreach ($cortes as $corte) {
            $notaRegistro = $notas->get($corte->id);
            $valores[$corte->numero] = ($notaRegistro && !is_null($notaRegistro->nota_cuantitativa))
                ? (int) round((float) $notaRegistro->nota_cuantitativa)
                : null;
            $indicadores[$corte->numero] = $notaRegistro?->indicadorLogro?->codigo;
        }

        $semestre1 = $this->calcularNotaSemestral($valores[1] ?? null, $valores[2] ?? null);
        $semestre2 = $this->calcularNotaSemestral($valores[3] ?? null, $valores[4] ?? null);
        $notaFinal = $this->calcularNotaFinal(
            $valores[1] ?? null,
            $valores[2] ?? null,
            $valores[3] ?? null,
            $valores[4] ?? null
        );

        return [
            'cortes' => $valores,
            'indicadores' => $indicadores,
            'semestre1' => $semestre1,
            'semestre2' => $semestre2,
            'promedio_general' => $this->calcularPromedioGeneral(array_values(array_filter($valores, fn ($v) => !is_null($v)))),
            'nota_final' => $notaFinal,
            'indicador_final' => $notaFinal !== null ? $this->calcularIndicadorLogro($notaFinal) : ($indicadores[4] ?? null),
            'aprobado' => $notaFinal !== null ? $this->estaAprobado($notaFinal) : null,
        ];
    }
}