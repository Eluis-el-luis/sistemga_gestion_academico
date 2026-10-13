<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\AsistenciaAula;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;

class ReporteService
{
    protected NotaService $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    public function anioActivo(): ?AnioEscolar
    {
        return AnioEscolar::where('activo', true)->first();
    }

    public function resolverAnio(?int $anioId): ?AnioEscolar
    {
        if ($anioId) {
            return AnioEscolar::find($anioId);
        }

        return $this->anioActivo() ?? AnioEscolar::orderBy('id', 'desc')->first();
    }

    /**
     * Catálogos para los filtros de búsqueda.
     */
    public function catalogos(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);

        return [
            'anios' => AnioEscolar::orderBy('id', 'desc')->get(),
            'modalidades' => Modalidad::all(),
            'grados' => Grado::orderBy('id')->get(),
            'asignaturas' => Asignatura::orderBy('nombre')->get(),
            // Los cortes se limitan al año escolar resuelto para evitar periodos de otros años.
            'cortes' => CorteEvaluativo::when($anio, fn ($q) => $q->where('anio_escolar_id', $anio->id))
                ->orderBy('numero')
                ->get(),
        ];
    }

    /**
     * Query base de asignaciones filtradas por año y criterios opcionales.
     */
    protected function queryAsignaciones(array $filtros)
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);

        $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario'])
            ->where('anio_escolar_id', $anio?->id);

        if (!empty($filtros['asignatura_id'])) {
            $query->where('asignatura_id', $filtros['asignatura_id']);
        }
        if (!empty($filtros['docente_id'])) {
            $query->where('docente_id', $filtros['docente_id']);
        }
        if (!empty($filtros['aula_id'])) {
            $query->where('aula_id', $filtros['aula_id']);
        }
        if (!empty($filtros['grado_id'])) {
            $query->whereHas('aula', fn ($q) => $q->where('grado_id', $filtros['grado_id']));
        }
        if (!empty($filtros['modalidad_id'])) {
            $query->whereHas('aula', fn ($q) => $q->where('modalidad_id', $filtros['modalidad_id']));
        }

        return [$query, $anio];
    }

    // =========================================================================
    // CONTROL DE INGRESO DE NOTAS
    // =========================================================================

    /**
     * Control de notas (ingresadas o pendientes) por asignación.
     */
    public function controlNotas(array $filtros): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        $asignaciones = $query->orderBy('aula_id')->get();

        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $tipo = $filtros['tipo'] ?? 'pendientes'; // 'pendientes' | 'ingresadas'

        $filas = [];
        foreach ($asignaciones as $asignacion) {
            $total = Matricula::where('aula_id', $asignacion->aula_id)->where('estado', 'activo')->count();

            $notasQuery = Nota::where('aula_asignatura_docente_id', $asignacion->id);
            if ($corteId) {
                $notasQuery->where('corte_evaluativo_id', $corteId);
            }
            $conNota = $notasQuery->distinct('matricula_id')->count('matricula_id');

            $pendientes = max(0, $total - $conNota);

            // Estado de cierre del parcial (fuente: corte_cerrado)
            $cerrado = \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $asignacion->id)
                ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
                ->where('bloqueado', true)
                ->exists();

            // filtro de tipo: si se pide solo ingresadas o solo pendientes
            if ($tipo === 'ingresadas' && $conNota === 0) continue;
            if ($tipo === 'pendientes' && $pendientes === 0) continue;

            $filas[] = [
                'aula' => $asignacion->aula->nombre,
                'grado' => $asignacion->aula->grado->nombre,
                'asignatura' => $asignacion->asignatura->nombre,
                'docente' => $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar',
                'total' => $total,
                'registradas' => $conNota,
                'pendientes' => $pendientes,
                'porcentaje' => $total > 0 ? round(($conNota / $total) * 100, 1) : 0,
                'cerrado' => $cerrado,
            ];
        }

        return $filas;
    }

    /**
     * Notas globales por alumno: una fila por alumno, con una columna por asignatura.
     * Devuelve la lista de asignaturas (columnas) y los alumnos con su nota por asignatura.
     * Si se filtra por corte, muestra la nota de ese corte; si no, el promedio final de la asignatura.
     */
    public function notasGlobalesPorAlumno(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);

        // Asignaciones del año (y filtros opcionales de grado/aula/asignatura/modalidad/docente)
        $asignaciones = \App\Models\AulaAsignaturaDocente::with('asignatura')
            ->where('anio_escolar_id', $anio?->id)
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('aula_id', $filtros['aula_id']))
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('modalidad_id', $filtros['modalidad_id'])))
            ->when(!empty($filtros['asignatura_id']), fn ($q) => $q->where('asignatura_id', $filtros['asignatura_id']))
            ->when(!empty($filtros['docente_id']), fn ($q) => $q->where('docente_id', $filtros['docente_id']))
            ->get();

        // Asignaturas únicas (columnas), ordenadas alfabéticamente
        $asignaturas = $asignaciones->pluck('asignatura')->unique('id')->values();

        // Matrículas activas de las aulas involucradas
        $aulaIds = $asignaciones->pluck('aula_id')->unique();
        $matriculas = Matricula::with('alumno', 'aula.grado')
            ->whereIn('aula_id', $aulaIds)
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->get();

        // Notas agrupadas por matrícula
        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $notas = Nota::with('aulaAsignaturaDocente.asignatura')
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get()
            ->groupBy('matricula_id');

        // Construir filas por alumno
        $filas = [];
        foreach ($matriculas as $matricula) {
            $notasAlumno = $notas->get($matricula->id, collect());

            // Por cada asignatura, calcular nota (promedio) o usar nota del corte
            $notasPorAsignatura = [];
            foreach ($asignaturas as $asignatura) {
                $notasDeAsignatura = $notasAlumno->filter(function ($n) use ($asignatura) {
                    return $n->aulaAsignaturaDocente->asignatura_id === $asignatura->id;
                });

                if ($notasDeAsignatura->isNotEmpty()) {
                    $notaFinal = $notasDeAsignatura->avg('nota_cuantitativa');
                    $notasPorAsignatura[$asignatura->id] = round((float) $notaFinal, 1);
                } else {
                    $notasPorAsignatura[$asignatura->id] = null;
                }
            }

            $filas[] = [
                'matricula_id' => $matricula->id,
                'alumno' => $matricula->alumno->nombre_completo,
                'cup' => $matricula->alumno->codigo_unico_persona,
                'grado' => $matricula->aula->grado->nombre ?? '',
                'notas' => $notasPorAsignatura,
            ];
        }

        return [
            'asignaturas' => $asignaturas,
            'filas' => $filas,
        ];
    }

    /**
     * Notas pendientes: matriculas activas sin nota en una asignación y corte.
     */
    public function notasPendientes(array $filtros)
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $docenteId = $filtros['docente_id'] ?? null;
        $asignaturaId = $filtros['asignatura_id'] ?? null;

        $matriculas = Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('aula_id', $filtros['aula_id']))
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('modalidad_id', $filtros['modalidad_id'])))
            ->get();

        $pendientes = [];

        foreach ($matriculas as $matricula) {
            $existe = Nota::where('matricula_id', $matricula->id)
                ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
                ->when($asignaturaId, fn ($q) => $q->whereHas('aulaAsignaturaDocente', fn ($q2) => $q2->where('asignatura_id', $asignaturaId)))
                ->when($docenteId, fn ($q) => $q->whereHas('aulaAsignaturaDocente', fn ($q2) => $q2->where('docente_id', $docenteId)))
                ->exists();

            if (!$existe) {
                $pendientes[] = $matricula;
            }
        }

        return collect($pendientes);
    }

    // =========================================================================
    // ASISTENCIA (segmentada)
    // =========================================================================

    /**
     * Asistencia global: resumen por aula para una fecha.
     */
    public function asistenciaGlobal(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fecha = $filtros['fecha'] ?? now()->toDateString();

        $aulas = Aula::with(['grado', 'modalidad'])
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->where('grado_id', $filtros['grado_id']))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->where('modalidad_id', $filtros['modalidad_id']))
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('id', $filtros['aula_id']))
            ->where('anio_escolar_id', $anio?->id)
            ->orderBy('turno')->orderBy('grado_id')->get();

        return $this->calcularAsistenciaPorAulas($aulas, $fecha);
    }

    protected function calcularAsistenciaPorAulas($aulas, string $fecha): array
    {
        $reporte = [];
        foreach ($aulas as $aula) {
            $matriculas = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->get();
            $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)->get()->keyBy('matricula_id');

            $presentes = 0; $ausentes = 0; $justificadas = 0;
            foreach ($matriculas as $m) {
                $e = $asistencias->get($m->id)?->estado_asistencia;
                if ($e === 'Presente' || $e === 'Actividad Institucional') $presentes++;
                elseif ($e === 'Ausencia Justificada') $justificadas++;
                else $ausentes++;
            }

            $total = $matriculas->count();
            $reporte[] = [
                'aula' => $aula->nombre, 'turno' => $aula->turno, 'grado' => $aula->grado->nombre,
                'modalidad' => $aula->modalidad->nombre, 'total' => $total,
                'presentes' => $presentes, 'ausentes' => $ausentes, 'justificadas' => $justificadas,
                'porcentaje' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }
        return $reporte;
    }

    /**
     * Estadísticas agregadas de asistencia por aula (rango de fechas).
     */
    public function estadisticasAsistencia(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $inicio = $filtros['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fin = $filtros['fecha_fin'] ?? now()->toDateString();

        $aulas = Aula::with(['grado', 'modalidad'])
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->where('grado_id', $filtros['grado_id']))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->where('modalidad_id', $filtros['modalidad_id']))
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('id', $filtros['aula_id']))
            ->where('anio_escolar_id', $anio?->id)->get();

        $reporte = [];
        foreach ($aulas as $aula) {
            $matriculas = Matricula::where('aula_id', $aula->id)->where('estado', 'activo')->get();
            $registros = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereBetween('fecha', [$inicio, $fin])->get();

            $totalRegistros = $registros->count();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $ausentes = $registros->where('estado_asistencia', 'Ausencia Injustificada')->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();

            $reporte[] = [
                'aula' => $aula->nombre, 'grado' => $aula->grado->nombre, 'modalidad' => $aula->modalidad->nombre,
                'total_registros' => $totalRegistros, 'presentes' => $presentes,
                'ausentes' => $ausentes, 'justificadas' => $justificadas,
                'porcentaje' => $totalRegistros > 0 ? round(($presentes / $totalRegistros) * 100, 1) : 0,
            ];
        }
        return $reporte;
    }

    /**
     * Estadísticas de asistencia por estudiante (en un aula y rango).
     */
    public function estadisticasPorEstudiante(array $filtros): array
    {
        $aulaId = $filtros['aula_id'] ?? null;
        $inicio = $filtros['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fin = $filtros['fecha_fin'] ?? now()->toDateString();

        $matriculas = Matricula::with('alumno')
            ->when($aulaId, fn ($q) => $q->where('aula_id', $aulaId))
            ->where('estado', 'activo')->get();

        $reporte = [];
        foreach ($matriculas as $m) {
            $registros = AsistenciaAula::where('matricula_id', $m->id)->whereBetween('fecha', [$inicio, $fin])->get();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $ausentes = $registros->where('estado_asistencia', 'Ausencia Injustificada')->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();
            $total = $registros->count();

            $reporte[] = [
                'alumno' => $m->alumno->nombre_completo,
                'cup' => $m->alumno->codigo_unico_persona,
                'total' => $total, 'presentes' => $presentes, 'ausentes' => $ausentes, 'justificadas' => $justificadas,
                'porcentaje' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }
        return $reporte;
    }

    // =========================================================================
    // RENDIMIENTO ACADÉMICO
    // =========================================================================

    /**
     * Notas por asignatura: promedio por asignación (aula-asignatura).
     */
    public function notasPorAsignatura(array $filtros): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $asignaciones = $query->orderBy('aula_id')->get();

        $reporte = [];
        foreach ($asignaciones as $asignacion) {
            $notasQuery = Nota::where('aula_asignatura_docente_id', $asignacion->id);
            if ($corteId) $notasQuery->where('corte_evaluativo_id', $corteId);
            $notas = $notasQuery->get();

            $promedio = (float) $notas->avg('nota_cuantitativa');
            $aprobados = $notas->where('nota_cuantitativa', '>=', 60)->count();
            $reprobados = $notas->where('nota_cuantitativa', '<', 60)->count();

            $reporte[] = [
                'aula' => $asignacion->aula->nombre,
                'grado' => $asignacion->aula->grado->nombre,
                'asignatura' => $asignacion->asignatura->nombre,
                'total' => $notas->count(),
                'promedio' => $notas->count() > 0 ? round($promedio, 2) : 0,
                'aprobados' => $aprobados,
                'reprobados' => $reprobados,
            ];
        }
        return $reporte;
    }

    /**
     * Rendimiento académico por corte (formato MINED / REA).
     * Por cada grado de una modalidad calcula: MI/MA (AS/F), aprobados en todas,
     * aplazados de 1/2/3+, docentes por grado, % aprobados y % retención.
     */
    public function rendimientoCorteMined(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $modalidadId = $filtros['modalidad_id'] ?? null;
        $gradoId = $filtros['grado_id'] ?? null;
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        $gradosQuery = Grado::with('modalidad')
            ->when($modalidadId, fn ($q) => $q->where('modalidad_id', $modalidadId))
            ->when($gradoId, fn ($q) => $q->where('id', $gradoId))
            ->orderBy('modalidad_id')
            ->orderBy('id')
            ->get();

        $filas = [];

        foreach ($gradosQuery as $grado) {
            $aulas = Aula::where('grado_id', $grado->id)
                ->where('anio_escolar_id', $anio?->id)
                ->get();

            $aulaIds = $aulas->pluck('id');

            // MI (Matrícula Inicial): TODAS las matrículas del año/grado (incluye retirados)
            $matriculasMI = Matricula::with('alumno')
                ->whereIn('aula_id', $aulaIds)
                ->where('anio_escolar_id', $anio?->id)
                ->get();

            // MA (Matrícula Actual): Solo matrículas ACTIVAS
            $matriculasMA = Matricula::with('alumno')
                ->whereIn('aula_id', $aulaIds)
                ->where('anio_escolar_id', $anio?->id)
                ->where('estado', 'activo')
                ->get();

            // Para el resto del reporte usamos matrículas activas (MA)
            $matriculas = $matriculasMA;

            // Asignaciones (para contar docentes por grado)
            $asignaciones = AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)
                ->where('anio_escolar_id', $anio?->id)
                ->get();
            $totalDocentes = $asignaciones->whereNotNull('docente_id')->pluck('docente_id')->unique()->count();

            // MI / MA por sexo
            $miAs = $matriculasMI->where('alumno.sexo', 'M')->count();
            $miF = $matriculasMI->where('alumno.sexo', 'F')->count();
            $maAs = $matriculasMA->where('alumno.sexo', 'M')->count();
            $maF = $matriculasMA->where('alumno.sexo', 'F')->count();

            // Clasificar alumnos por nº de asignaturas reprobadas
            $aprobadosTodasAs = 0; $aprobadosTodasF = 0;
            $aplazados1As = 0; $aplazados1F = 0;
            $aplazados2As = 0; $aplazados2F = 0;
            $aplazados3As = 0; $aplazados3F = 0;

            $matriculaIds = $matriculas->pluck('id');
            $notas = Nota::whereIn('matricula_id', $matriculaIds)
                ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
                ->get()
                ->groupBy('matricula_id');

            foreach ($matriculas as $matricula) {
                $notasAlumno = $notas->get($matricula->id, collect());

                if ($notasAlumno->isEmpty()) {
                    continue; // sin notas → no se clasifica
                }

                $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                $esM = $matricula->alumno->sexo === 'M';

                if ($reprobadas === 0) {
                    $esM ? $aprobadosTodasAs++ : $aprobadosTodasF++;
                } elseif ($reprobadas === 1) {
                    $esM ? $aplazados1As++ : $aplazados1F++;
                } elseif ($reprobadas === 2) {
                    $esM ? $aplazados2As++ : $aplazados2F++;
                } else {
                    $esM ? $aplazados3As++ : $aplazados3F++;
                }
            }

            $totalEvaluados = $aprobadosTodasAs + $aprobadosTodasF + $aplazados1As + $aplazados1F + $aplazados2As + $aplazados2F + $aplazados3As + $aplazados3F;

            $filas[] = [
                'grado' => $grado->nombre,
                'modalidad' => $grado->modalidad->nombre ?? '',
                'mi_as' => $miAs,
                'mi_f' => $miF,
                'ma_as' => $maAs,
                'ma_f' => $maF,
                'aprobados_todas_as' => $aprobadosTodasAs,
                'aprobados_todas_f' => $aprobadosTodasF,
                'aplazados_1_as' => $aplazados1As,
                'aplazados_1_f' => $aplazados1F,
                'aplazados_2_as' => $aplazados2As,
                'aplazados_2_f' => $aplazados2F,
                'aplazados_3_as' => $aplazados3As,
                'aplazados_3_f' => $aplazados3F,
                'total_docentes' => $totalDocentes,
                'porcentaje_aprobados' => $totalEvaluados > 0 ? round((($aprobadosTodasAs + $aprobadosTodasF) / $totalEvaluados) * 100, 1) : 0,
'porcentaje_retencion' => ($miAs + $miF) > 0 ? round((($maAs + $maF) / ($miAs + $miF)) * 100, 1) : 0.0,
            ];
        }

        return [
            'filas' => $filas,
            'modalidad' => $modalidadId ? Modalidad::find($modalidadId)?->nombre : null,
        ];
    }

    /**
     * Rendimiento por corte de un aula específica (para el docente guía).
     * Devuelve un resumen del grado del aula: MI/MA (AS/F), aprobados en todas,
     * aplazados de 1/2/3+, total de docentes, % aprobados y % retención.
     */
    public function rendimientoAula(Aula $aula, ?int $corteId = null): array
    {
        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $aula->id)
            ->where('anio_escolar_id', $aula->anio_escolar_id)
            ->where('estado', 'activo')
            ->get();

        $asignaciones = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('anio_escolar_id', $aula->anio_escolar_id)
            ->get();
        $totalDocentes = $asignaciones->whereNotNull('docente_id')->pluck('docente_id')->unique()->count();

        // MI/MA por sexo
        $miAs = $matriculas->where('alumno.sexo', 'M')->count();
        $miF = $matriculas->where('alumno.sexo', 'F')->count();

        $notas = Nota::whereIn('matricula_id', $matriculas->pluck('id'))
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get()
            ->groupBy('matricula_id');

        $aprobadosTodas = 0;
        $aplazados1 = 0;
        $aplazados2 = 0;
        $aplazados3 = 0;

        foreach ($matriculas as $matricula) {
            $notasAlumno = $notas->get($matricula->id, collect());
            if ($notasAlumno->isEmpty()) continue;

            $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
            if ($reprobadas === 0) $aprobadosTodas++;
            elseif ($reprobadas === 1) $aplazados1++;
            elseif ($reprobadas === 2) $aplazados2++;
            else $aplazados3++;
        }

        $totalEvaluados = $aprobadosTodas + $aplazados1 + $aplazados2 + $aplazados3;

        return [
            'grado' => $aula->grado->nombre ?? '',
            'seccion' => $aula->nombre,
            'mi_as' => $miAs,
            'mi_f' => $miF,
            'aprobados_todas' => $aprobadosTodas,
            'aplazados_1' => $aplazados1,
            'aplazados_2' => $aplazados2,
            'aplazados_3' => $aplazados3,
            'total_docentes' => $totalDocentes,
            'porcentaje_aprobados' => $totalEvaluados > 0 ? round(($aprobadosTodas / $totalEvaluados) * 100, 1) : 0,
            'porcentaje_retencion' => ($miAs + $miF) > 0 ? 100.0 : 0.0,
        ];
    }

    /**
     * Certificado de Notas (formato MINED) de un estudiante.
     *
     * Agrupa el historial de matrículas del estudiante y devuelve las notas
     * finales (NF cuantitativa + equivalencia cualitativa AA/AS/AF/AI) del
     * grado seleccionado en el año indicado más los grados inmediatamente
     * anteriores MISMO si aprobó o reprobó.
     *
     * El parámetro $limiteGrados (y la constante CERTIFICADO_LIMITE_GRADOS)
     * es la "flexibilidad oculta": por defecto son 2 grados (seleccionado +
     * anterior), pero basta aumentar el valor para ampliar el histórico.
     */
    public const CERTIFICADO_LIMITE_GRADOS = 2;

    public function certificadoNotas(int $alumnoId, ?int $anioId = null, ?int $gradoId = null, int $limiteGrados = self::CERTIFICADO_LIMITE_GRADOS): array
    {
        $vacio = [
            'alumno' => null,
            'anio' => null,
            'matricula' => null,
            'gradoSeleccionado' => null,
            'grados' => [],
            'asignaturas' => [],
            'limiteGrados' => $limiteGrados,
        ];

        $alumno = Alumno::find($alumnoId);
        if (!$alumno) {
            return $vacio;
        }

        $anio = $this->resolverAnio($anioId);

        // Grado de partida: el filtro seleccionado o el de la matrícula del año
        $gradoBase = $gradoId ? Grado::with('modalidad')->find($gradoId) : null;

        $matriculaBase = Matricula::with(['aula.grado.modalidad', 'anioEscolar'])
            ->where('alumno_id', $alumno->id)
            ->when($anio, fn ($q) => $q->where('anio_escolar_id', $anio->id))
            ->when($gradoBase, fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $gradoBase->id)))
            ->latest('id')
            ->first();

        if (!$gradoBase) {
            $gradoBase = $matriculaBase?->aula?->grado;
        }

        if (!$gradoBase) {
            // El estudiante no tiene matrícula en ese año/grado
            return array_merge($vacio, ['alumno' => $alumno, 'anio' => $anio]);
        }

        // Cadena de grados (misma modalidad): seleccionado + anteriores,
        // ordenada del menor al mayor para la tabla comparativa.
        $limiteGrados = max(1, $limiteGrados);
        $cadena = [$gradoBase];
        $actual = $gradoBase;
        for ($i = 1; $i < $limiteGrados; $i++) {
            $anterior = Grado::where('modalidad_id', $actual->modalidad_id)
                ->where('id', '<', $actual->id)
                ->orderBy('id', 'desc')
                ->first();
            if (!$anterior) {
                break;
            }
            array_unshift($cadena, $anterior);
            $actual = $anterior;
        }

        // Escala cualitativa por modalidad (código => nombre del indicador)
        $escala = IndicadorLogro::where('modalidad_id', $gradoBase->modalidad_id)
            ->pluck('nombre', 'codigo');

        $grados = [];
        $nombresAsignaturas = [];

        foreach ($cadena as $grado) {
            // El grado seleccionado respeta el año filtrado; los anteriores toman
            // la matrícula histórica del alumno en ese grado (cualquier año).
            if ($grado->id === $gradoBase->id) {
                $matricula = $matriculaBase ?? Matricula::with(['aula.grado.modalidad', 'anioEscolar'])
                    ->where('alumno_id', $alumno->id)
                    ->when($anio, fn ($q) => $q->where('anio_escolar_id', $anio->id))
                    ->whereHas('aula', fn ($q2) => $q2->where('grado_id', $grado->id))
                    ->latest('id')
                    ->first();
            } else {
                $matricula = Matricula::with(['aula.grado.modalidad', 'anioEscolar'])
                    ->where('alumno_id', $alumno->id)
                    ->whereHas('aula', fn ($q) => $q->where('grado_id', $grado->id))
                    ->latest('id')
                    ->first();
            }

            $notas = [];
            $finales = [];

            if ($matricula) {
                $asignaciones = AulaAsignaturaDocente::with('asignatura')
                    ->where('aula_id', $matricula->aula_id)
                    ->where('anio_escolar_id', $matricula->anio_escolar_id)
                    ->get()
                    ->sortBy('asignatura.nombre');

                foreach ($asignaciones as $asignacion) {
                    // NF del año REAL de la matrícula: se promedian los cortes
                    // registrados de esa matrícula (sin depender del año activo),
                    // para que los grados de años pasados también muestren su NF.
                    $cortes = Nota::where('matricula_id', $matricula->id)
                        ->where('aula_asignatura_docente_id', $asignacion->id)
                        ->pluck('nota_cuantitativa')
                        ->filter(fn ($n) => !is_null($n));

                    $cuan = $cortes->isNotEmpty() ? (int) round($cortes->avg()) : null;
                    $cua = $cuan !== null ? $this->notaService->calcularIndicadorLogro($cuan) : null;

                    $nombre = $asignacion->asignatura->nombre;
                    $notas[$nombre] = [
                        'cuan' => $cuan,
                        'cua' => $cua,
                        'cua_nombre' => $cua ? ($escala[$cua] ?? $cua) : null,
                    ];
                    $nombresAsignaturas[] = $nombre;
                    if ($cuan !== null) {
                        $finales[] = $cuan;
                    }
                }
            }

            $promedio = count($finales) > 0 ? round(array_sum($finales) / count($finales), 2) : null;
            $promedioCua = $promedio !== null ? $this->notaService->calcularIndicadorLogro((int) round($promedio)) : null;

            $grados[] = [
                'grado' => $grado,
                'anioEscolar' => $matricula?->anioEscolar,
                'matricula' => $matricula,
                'promedio' => $promedio,
                'promedio_cua' => $promedioCua,
                'promedio_cua_nombre' => $promedioCua ? ($escala[$promedioCua] ?? $promedioCua) : null,
                'notas' => $notas,
            ];
        }

        // Unión ordenada alfabéticamente de las asignaturas de todos los grados
        $nombresAsignaturas = array_values(array_unique($nombresAsignaturas));
        sort($nombresAsignaturas);
        $asignaturas = array_map(fn ($nombre) => ['nombre' => $nombre], $nombresAsignaturas);

        return [
            'alumno' => $alumno,
            'anio' => $anio,
            'matricula' => $matriculaBase ?? ($grados ? ($grados[array_key_last($grados)]['matricula']) : null),
            'gradoSeleccionado' => $gradoBase,
            'grados' => $grados,
            'asignaturas' => $asignaturas,
            'limiteGrados' => $limiteGrados,
        ];
    }

    // =========================================================================
    // RESUMEN PARA EL HUB
    // =========================================================================

    public function resumenIngresoNotas(?int $anioId): array
    {
        $filas = $this->controlNotas(['anio_escolar_id' => $anioId, 'tipo' => 'pendientes']);
        $totalPendientes = array_sum(array_column($filas, 'pendientes'));
        $totalRegistradas = array_sum(array_column($filas, 'registradas'));
        $total = array_sum(array_column($filas, 'total'));

        return [
            'asignaciones' => count($filas),
            'total_notas_esperadas' => $total,
            'notas_registradas' => $totalRegistradas,
            'notas_pendientes' => $totalPendientes,
            'porcentaje' => $total > 0 ? round(($totalRegistradas / $total) * 100, 1) : 0,
        ];
    }

    public function estudiantes(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);
        $anioId = $anio?->id;

        return [
            'total_alumnos' => Alumno::count(),
            'matriculados' => Matricula::where('anio_escolar_id', $anioId)->where('estado', 'activo')->count(),
            'retirados' => Matricula::where('anio_escolar_id', $anioId)->where('estado', 'retirado')->count(),
            'expedientes_incompletos' => Alumno::where(function ($q) {
                $q->whereNull('direccion_domiciliar')->orWhereNull('madre_nombre_completo')
                  ->orWhereNull('madre_telefono')->orWhereNull('tutor_nombre_completo')->orWhereNull('fecha_nacimiento');
            })->count(),
        ];
    }

    public function mined(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);
        $datos = [];
        foreach (Modalidad::all() as $modalidad) {
            $matriculados = Matricula::where('anio_escolar_id', $anio?->id)
                ->whereHas('aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->where('estado', 'activo')->count();
            $retirados = Matricula::where('anio_escolar_id', $anio?->id)
                ->whereHas('aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->where('estado', 'retirado')->count();
            $promedio = Nota::whereHas('matricula.aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->whereHas('matricula', fn ($q) => $q->where('anio_escolar_id', $anio?->id))
                ->avg('nota_cuantitativa');

            $datos[] = [
                'modalidad' => $modalidad->nombre,
                'matriculados' => $matriculados,
                'retirados' => $retirados,
                'promedio' => $promedio ? round((float) $promedio, 2) : 0,
                'retencion' => ($matriculados + $retirados) > 0 ? round(($matriculados / ($matriculados + $retirados)) * 100, 1) : 0,
            ];
        }
        return $datos;
    }

    public function padres(): array
    {
        $alumnos = Alumno::all();
        $conTelefono = $alumnos->filter(fn ($a) => !empty($a->madre_telefono) || !empty($a->padre_telefono) || !empty($a->tutor_telefono))->count();
        $conEmail = $alumnos->filter(fn ($a) => $a->usuario && !empty($a->usuario->email))->count();
        return [
            'total' => $alumnos->count(),
            'con_telefono' => $conTelefono,
            'con_email' => $conEmail,
            'porcentaje_adopcion' => $alumnos->count() > 0 ? round(($conTelefono / $alumnos->count()) * 100, 1) : 0,
        ];
    }
}