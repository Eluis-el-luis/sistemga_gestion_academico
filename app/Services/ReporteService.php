<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\AsistenciaAula;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\AvanceContenido;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\ExamenReparacion;
use App\Models\Grado;
use App\Models\IndicadorLogro;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;

class ReporteService
{
    public const CERTIFICADO_LIMITE_GRADOS = 2;

    protected NotaService $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    public function anioActivo(): ?AnioEscolar
    {
        return AnioEscolar::where('estado', 'activo')->first();
    }

    public function resolverAnio(?int $anioId): ?AnioEscolar
    {
        if ($anioId) {
            return AnioEscolar::find($anioId);
        }

        return $this->anioActivo() ?? AnioEscolar::orderByDesc('id')->first();
    }

    public function catalogos(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);

        return [
            'anios' => AnioEscolar::orderByDesc('id')->get(),
            'modalidades' => Modalidad::all(),
            'grados' => Grado::orderBy('id')->get(),
            'asignaturas' => Asignatura::orderBy('nombre')->get(),
            'cortes' => CorteEvaluativo::when($anio, fn ($q) => $q->where('anio_escolar_id', $anio->id))
                ->orderBy('numero')
                ->get(),
        ];
    }

    protected function queryAsignaciones(array $filtros)
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);

        $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario'])
            ->where('anio_escolar_id', $anio?->id);

        if (!empty($filtros['aula_id'])) {
            $query->where('aula_id', $filtros['aula_id']);
        }
        if (!empty($filtros['asignatura_id'])) {
            $query->where('asignatura_id', $filtros['asignatura_id']);
        }
        if (!empty($filtros['docente_id'])) {
            $query->where('docente_id', $filtros['docente_id']);
        }
        if (!empty($filtros['grado_id'])) {
            $query->whereHas('aula', fn ($q) => $q->where('grado_id', $filtros['grado_id']));
        }
        if (!empty($filtros['modalidad_id'])) {
            $query->whereHas('aula', fn ($q) => $q->where('modalidad_id', $filtros['modalidad_id']));
        }

        return [$query, $anio];
    }

    public function asistenciaPorSeccion(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fecha = $filtros['fecha'] ?? now()->toDateString();
        $aulaId = $filtros['aula_id'] ?? null;

        $aula = null;
        $filas = [];
        $totales = ['presentes' => 0, 'ausentes' => 0, 'justificadas' => 0, 'sin_registrar' => 0, 'total' => 0];

        if ($aulaId) {
            $aula = Aula::with('grado')->find($aulaId);

            $matriculas = Matricula::with('alumno')
                ->where('anio_escolar_id', $anio?->id)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

            $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)
                ->get()
                ->keyBy('matricula_id');

            $totales['total'] = $matriculas->count();

            foreach ($matriculas as $matricula) {
                $registro = $asistencias->get($matricula->id);
                $estado = $registro ? $registro->estado_asistencia : 'Sin Registrar';

                if ($estado === 'Presente' || $estado === 'Actividad Institucional') {
                    $totales['presentes']++;
                    $colorTema = 'text-emerald-700 bg-emerald-50 border-emerald-200';
                } elseif ($estado === 'Ausencia Justificada') {
                    $totales['justificadas']++;
                    $colorTema = 'text-amber-700 bg-amber-50 border-amber-200';
                } elseif (in_array($estado, ['Ausencia', 'Ausencia Injustificada'], true)) {
                    $totales['ausentes']++;
                    $colorTema = 'text-rose-700 bg-rose-50 border-rose-200';
                } else {
                    $totales['sin_registrar']++;
                    $colorTema = 'text-slate-600 bg-slate-100 border-slate-300';
                }

                $filas[] = [
                    'estudiante' => $matricula->alumno->nombre_completo,
                    'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                    'estado' => $estado,
                    'observacion' => ($registro && !empty($registro->observaciones)) ? $registro->observaciones : '-',
                    'color_tema' => $colorTema,
                ];
            }
        }

        $porcentaje = $totales['total'] > 0 ? round(($totales['presentes'] / $totales['total']) * 100, 1) : 0;

        return [
            'anio' => $anio,
            'fecha' => $fecha,
            'aula' => $aula,
            'filas' => $filas,
            'totales' => $totales,
            'porcentaje' => $porcentaje,
        ];
    }

    public function controlNotas(array $filtros): array
    {
        [$query] = $this->queryAsignaciones($filtros);

        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $tipo = $filtros['tipo'] ?? 'todos';

        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        $asignacionIds = $asignaciones->pluck('id');

        if ($asignacionIds->isEmpty()) {
            return [];
        }

        $notasAgrupadas = Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->selectRaw('aula_asignatura_docente_id, count(distinct matricula_id) as registradas')
            ->groupBy('aula_asignatura_docente_id')
            ->pluck('registradas', 'aula_asignatura_docente_id');

        $aulaIds = $asignaciones->pluck('aula_id')->unique();
        $matriculasPorAula = Matricula::whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->selectRaw('aula_id, count(*) as total')
            ->groupBy('aula_id')
            ->pluck('total', 'aula_id');

        $cortesCerrados = CorteCerrado::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->where('bloqueado', true)
            ->pluck('aula_asignatura_docente_id')
            ->flip();

        $filas = [];
        foreach ($asignaciones as $asignacion) {
            $total = (int) $matriculasPorAula->get($asignacion->aula_id, 0);
            $conNota = (int) $notasAgrupadas->get($asignacion->id, 0);
            $pendientes = max(0, $total - $conNota);
            $cerrado = $cortesCerrados->has($asignacion->id);

            if ($tipo === 'ingresadas' && $conNota === 0) {
                continue;
            }
            if ($tipo === 'pendientes' && $pendientes === 0) {
                continue;
            }

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

        return collect($filas)->sort(function ($a, $b) {
            $cmpGrado = strcmp($a['grado'], $b['grado']);
            if ($cmpGrado !== 0) {
                return $cmpGrado;
            }
            $cmpAula = strcmp($a['aula'], $b['aula']);
            if ($cmpAula !== 0) {
                return $cmpAula;
            }

            return strcmp($a['asignatura'], $b['asignatura']);
        })->values()->all();
    }

    public function notasGlobalesPorAlumno(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $aulaId = $filtros['aula_id'] ?? null;
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        $asignaciones = AulaAsignaturaDocente::with('asignatura')
            ->where('anio_escolar_id', $anio?->id)
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('aula_id', $filtros['aula_id']))
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('modalidad_id', $filtros['modalidad_id'])))
            ->when(!empty($filtros['asignatura_id']), fn ($q) => $q->where('asignatura_id', $filtros['asignatura_id']))
            ->when(!empty($filtros['docente_id']), fn ($q) => $q->where('docente_id', $filtros['docente_id']))
            ->get();

        $aula = $aulaId ? Aula::with('grado')->find($aulaId) : null;

        $asignaturas = $asignaciones->pluck('asignatura')->unique('id')
            ->reject(function ($asig) {
                $nombreNormalizado = str_replace(
                    ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'],
                    ['A', 'E', 'I', 'O', 'U', 'U'],
                    mb_strtoupper(trim($asig->nombre), 'UTF-8')
                );

                return in_array($nombreNormalizado, ['TIC', 'BIBLIA', 'INFORMATICA'], true);
            })
            ->sortBy('nombre')->values();

        $aulasInvolucradas = $asignaciones->pluck('aula_id')->unique();
        $matriculas = Matricula::with('alumno')
            ->whereIn('aula_id', $aulasInvolucradas)
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->get()
            ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

        $notas = Nota::with('aulaAsignaturaDocente')
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get()
            ->groupBy('matricula_id');

        $filas = [];
        foreach ($matriculas as $matricula) {
            $notasAlumno = $notas->get($matricula->id, collect());
            $notasPorAsignatura = [];
            $sumaPromedio = 0;
            $materiasContadas = 0;
            $reprobadas = 0;

            foreach ($asignaturas as $asignatura) {
                $notasDeAsignatura = $notasAlumno->filter(
                    fn ($n) => (int) ($n->aulaAsignaturaDocente?->asignatura_id ?? 0) === (int) $asignatura->id
                );

                if ($notasDeAsignatura->isNotEmpty()) {
                    $notaFinal = (int) round((float) $notasDeAsignatura->avg('nota_cuantitativa'));
                    $notasPorAsignatura[$asignatura->id] = $notaFinal;
                    $sumaPromedio += $notaFinal;
                    $materiasContadas++;

                    if ($notaFinal < 60) {
                        $reprobadas++;
                    }
                } else {
                    $notasPorAsignatura[$asignatura->id] = null;
                }
            }

            $filas[] = [
                'matricula_id' => $matricula->id,
                'alumno' => $matricula->alumno->nombre_completo,
                'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                'notas' => $notasPorAsignatura,
                'promedio_general' => $materiasContadas > 0 ? round($sumaPromedio / $materiasContadas, 1) : 0,
                'reprobadas' => $reprobadas,
            ];
        }

        return [
            'aula' => $aula,
            'asignaturas' => $asignaturas,
            'filas' => $filas,
        ];
    }

    public function notasPendientes(array $filtros): array
    {
        [$query] = $this->queryAsignaciones($filtros);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        if ($asignaciones->isEmpty()) {
            return [];
        }

        $aulaIds = $asignaciones->pluck('aula_id')->unique();
        $matriculas = Matricula::with('alumno')
            ->whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->get()
            ->groupBy('aula_id');

        $asignacionIds = $asignaciones->pluck('id');
        $notasExistentes = Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get(['matricula_id', 'aula_asignatura_docente_id'])
            ->groupBy('aula_asignatura_docente_id');

        $pendientes = [];

        foreach ($asignaciones as $asignacion) {
            $alumnosAula = $matriculas->get($asignacion->aula_id, []);
            $conNotaIds = $notasExistentes->get($asignacion->id, collect())->pluck('matricula_id')->toArray();

            foreach ($alumnosAula as $matricula) {
                if (!in_array($matricula->id, $conNotaIds, true)) {
                    $pendientes[] = [
                        'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                        'estudiante' => $matricula->alumno->nombre_completo,
                        'grado_aula' => ($asignacion->aula->grado->nombre ?? '') . ' - ' . $asignacion->aula->nombre,
                        'asignatura' => $asignacion->asignatura->nombre,
                        'docente' => $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar',
                    ];
                }
            }
        }

        return collect($pendientes)->sortBy([
            ['docente', 'asc'],
            ['asignatura', 'asc'],
            ['estudiante', 'asc'],
        ])->values()->all();
    }

    public function asistenciaGlobal(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fecha = $filtros['fecha'] ?? now()->toDateString();

        $aulas = Aula::with(['grado', 'modalidad'])
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->where('grado_id', $filtros['grado_id']))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->where('modalidad_id', $filtros['modalidad_id']))
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('id', $filtros['aula_id']))
            ->where('anio_escolar_id', $anio?->id)
            ->orderBy('turno')
            ->orderBy('grado_id')
            ->get();

        if ($aulas->isEmpty()) {
            return ['fecha' => $fecha, 'filas' => []];
        }

        $aulaIds = $aulas->pluck('id');
        $matriculas = Matricula::whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->get(['id', 'aula_id']);

        $matriculasPorAula = $matriculas->groupBy('aula_id');

        $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
            ->where('fecha', $fecha)
            ->get(['matricula_id', 'estado_asistencia'])
            ->keyBy('matricula_id');

        $reporte = [];
        foreach ($aulas as $aula) {
            $matriculasAula = $matriculasPorAula->get($aula->id, collect());
            $total = $matriculasAula->count();
            $presentes = 0;
            $ausentes = 0;
            $justificadas = 0;

            foreach ($matriculasAula as $m) {
                $estado = $asistencias->get($m->id)?->estado_asistencia;

                if ($estado === 'Presente' || $estado === 'Actividad Institucional') {
                    $presentes++;
                } elseif ($estado === 'Ausencia Justificada') {
                    $justificadas++;
                } else {
                    $ausentes++;
                }
            }

            $reporte[] = [
                'aula' => ($aula->grado->nombre ?? '') . ' - ' . $aula->nombre,
                'turno' => $aula->turno ?? 'No definido',
                'modalidad' => $aula->modalidad->nombre ?? 'Regular',
                'total' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }

        return [
            'fecha' => $fecha,
            'filas' => collect($reporte)->sortByDesc('porcentaje')->values()->all(),
        ];
    }

    public function auditoriaAvanceContenido(array $filtros, string $perspectiva = 'docente'): array
    {
        [$query] = $this->queryAsignaciones($filtros);
        $asignaciones = $query->get();

        $reporte = [];
        foreach ($asignaciones as $asignacion) {
            $avanceModel = AvanceContenido::where('aula_asignatura_docente_id', $asignacion->id)->latest('id')->first();
            $porcentaje = $avanceModel ? (float) $avanceModel->porcentaje_avance : 0.0;
            $unidadesImpartidas = $avanceModel->unidades_impartidas ?? 'No registrado';

            $base = [
                'docente' => $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar',
                'asignatura' => $asignacion->asignatura->nombre,
                'grado' => ($asignacion->aula->grado->nombre ?? '') . ' - Sección "' . $asignacion->aula->nombre . '"',
                'detalle' => $unidadesImpartidas,
                'porcentaje' => $porcentaje,
            ];

            $reporte[] = $base;
        }

        $coleccion = collect($reporte);

        return $perspectiva === 'docente'
            ? $coleccion->sortBy('docente')->values()->all()
            : $coleccion->sortBy('grado')->values()->all();
    }

    public function estadisticasAsistencia(array $filtros): array
    {
        return $this->estadisticasFechas($filtros);
    }

    public function estadisticasPorEstudiante(array $filtros): array
    {
        return $this->asistenciaEstudiante($filtros);
    }

    public function notasPorAsignatura(array $filtros): array
    {
        [$query] = $this->queryAsignaciones($filtros);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        $asignacionIds = $asignaciones->pluck('id');

        if ($asignacionIds->isEmpty()) {
            return [];
        }

        $notas = Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get(['aula_asignatura_docente_id', 'nota_cuantitativa'])
            ->groupBy('aula_asignatura_docente_id');

        $agrupado = [];
        foreach ($asignaciones as $asignacion) {
            $docenteNombre = $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar';
            $asignaturaNombre = $asignacion->asignatura->nombre;
            $gradoSeccion = ($asignacion->aula->grado->nombre ?? '') . ' "' . ($asignacion->aula->nombre ?? '') . '"';
            $key = $docenteNombre . '|' . $asignaturaNombre;
            $notasAsignacion = $notas->get($asignacion->id, collect());

            if (!isset($agrupado[$key])) {
                $agrupado[$key] = [
                    'docente' => $docenteNombre,
                    'asignatura' => $asignaturaNombre,
                    'aulas' => [],
                    'evaluados' => 0,
                    'aprobados' => 0,
                    'reprobados' => 0,
                    'suma_notas' => 0,
                ];
            }

            if (!in_array($gradoSeccion, $agrupado[$key]['aulas'], true)) {
                $agrupado[$key]['aulas'][] = $gradoSeccion;
            }

            $agrupado[$key]['evaluados'] += $notasAsignacion->count();
            $agrupado[$key]['aprobados'] += $notasAsignacion->where('nota_cuantitativa', '>=', 60)->count();
            $agrupado[$key]['reprobados'] += $notasAsignacion->where('nota_cuantitativa', '<', 60)->count();
            $agrupado[$key]['suma_notas'] += (float) $notasAsignacion->sum('nota_cuantitativa');
        }

        $reporte = [];
        foreach ($agrupado as $item) {
            $evaluados = $item['evaluados'];

            $reporte[] = [
                'docente' => $item['docente'],
                'asignatura' => $item['asignatura'],
                'aulas' => implode(', ', $item['aulas']),
                'evaluados' => $evaluados,
                'aprobados' => $item['aprobados'],
                'reprobados' => $item['reprobados'],
                'tasa_riesgo' => $evaluados > 0 ? round(($item['reprobados'] / $evaluados) * 100, 1) : 0,
                'promedio' => $evaluados > 0 ? round($item['suma_notas'] / $evaluados, 2) : 0,
            ];
        }

        return collect($reporte)->sortBy([
            ['docente', 'asc'],
            ['asignatura', 'asc'],
        ])->values()->all();
    }

    public function rendimientoCorteMined(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $modalidadId = $filtros['modalidad_id'] ?? null;
        $gradoId = $filtros['grado_id'] ?? null;
        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $corte = $corteId ? CorteEvaluativo::find($corteId) : null;

        $gradosQuery = Grado::with('modalidad')
            ->when($modalidadId, fn ($q) => $q->where('modalidad_id', $modalidadId))
            ->when($gradoId, fn ($q) => $q->where('id', $gradoId))
            ->orderBy('modalidad_id')
            ->orderBy('id')
            ->get();

        $secciones = [];
        foreach ($gradosQuery as $grado) {
            $aulaIds = Aula::where('grado_id', $grado->id)
                ->where('anio_escolar_id', $anio?->id)
                ->pluck('id');

            $matriculasMI = Matricula::with('alumno')
                ->whereIn('aula_id', $aulaIds)
                ->where('anio_escolar_id', $anio?->id)
                ->get();
            $matriculasMA = $matriculasMI->where('estado', 'activo');
            $matriculaIds = $matriculasMA->pluck('id');

            $notas = Nota::whereIn('matricula_id', $matriculaIds)
                ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
                ->get()
                ->groupBy('matricula_id');

            $reparacionIds = ExamenReparacion::whereIn('matricula_id', $matriculaIds)
                ->when($corte, fn ($q) => $q->whereBetween('fecha', [$corte->fecha_inicio, $corte->fecha_fin]))
                ->pluck('matricula_id')
                ->unique();

            $fila = [
                'grado' => $grado->nombre,
                'mi_as' => $matriculasMI->count(),
                'mi_f' => $matriculasMI->where('alumno.sexo', 'F')->count(),
                'ma_as' => $matriculasMA->count(),
                'ma_f' => $matriculasMA->where('alumno.sexo', 'F')->count(),
                'aprob_as' => 0, 'aprob_f' => 0,
                'aplaz12_as' => 0, 'aplaz12_f' => 0,
                'aplaz3_as' => 0, 'aplaz3_f' => 0,
                'eval_oficial_as' => 0, 'eval_oficial_f' => 0,
                'eval_extra_as' => $reparacionIds->count(),
                'eval_extra_f' => $matriculasMA->whereIn('id', $reparacionIds)->where('alumno.sexo', 'F')->count(),
                'no_evaluado_as' => 0, 'no_evaluado_f' => 0,
                'asist_ref_as' => 0, 'asist_ref_f' => 0,
                'maest_ref_as' => 0, 'maest_ref_f' => 0,
                'aa_as' => 0, 'aa_f' => 0,
                'as_as' => 0, 'as_f' => 0,
                'ae_as' => 0, 'ae_f' => 0,
                'ai_as' => 0, 'ai_f' => 0,
            ];

            foreach ($matriculasMA as $matricula) {
                $notasAlumno = $notas->get($matricula->id);
                $esF = $matricula->alumno->sexo === 'F';

                if (!$notasAlumno || $notasAlumno->isEmpty()) {
                    $fila['no_evaluado_as']++;
                    if ($esF) {
                        $fila['no_evaluado_f']++;
                    }
                    continue;
                }

                $fila['eval_oficial_as']++;
                if ($esF) {
                    $fila['eval_oficial_f']++;
                }

                $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                if ($reprobadas === 0) {
                    $fila['aprob_as']++;
                    if ($esF) {
                        $fila['aprob_f']++;
                    }
                } elseif ($reprobadas <= 2) {
                    $fila['aplaz12_as']++;
                    if ($esF) {
                        $fila['aplaz12_f']++;
                    }
                } else {
                    $fila['aplaz3_as']++;
                    if ($esF) {
                        $fila['aplaz3_f']++;
                    }
                }

                $banda = $this->notaService->calcularIndicadorLogro((int) round((float) $notasAlumno->avg('nota_cuantitativa')));
                $clave = match ($banda) {
                    'AA' => 'aa',
                    'AS' => 'as',
                    'AE' => 'ae',
                    'AI' => 'ai',
                    default => null,
                };
                if ($clave) {
                    $fila["{$clave}_as"]++;
                    if ($esF) {
                        $fila["{$clave}_f"]++;
                    }
                }
            }

            $fila['perm_as'] = $fila['mi_as'] > 0 ? round(($fila['ma_as'] / $fila['mi_as']) * 100, 1) : 0;
            $fila['perm_f'] = $fila['mi_f'] > 0 ? round(($fila['ma_f'] / $fila['mi_f']) * 100, 1) : 0;
            $fila['aprob_pct_as'] = $fila['eval_oficial_as'] > 0 ? round(($fila['aprob_as'] / $fila['eval_oficial_as']) * 100, 1) : 0;
            $fila['aprob_pct_f'] = $fila['eval_oficial_f'] > 0 ? round(($fila['aprob_f'] / $fila['eval_oficial_f']) * 100, 1) : 0;
            $fila['valor_as'] = $fila['aa_as'] + $fila['as_as'] + $fila['ae_as'] + $fila['ai_as'];
            $fila['valor_f'] = $fila['aa_f'] + $fila['as_f'] + $fila['ae_f'] + $fila['ai_f'];

            $nombreModalidad = $grado->modalidad->nombre ?? 'Sin modalidad';
            $secciones[$nombreModalidad]['modalidad'] = $nombreModalidad;
            $secciones[$nombreModalidad]['filas'][] = $fila;
        }

        foreach ($secciones as $nombre => $seccion) {
            $total = ['grado' => 'TOTAL'];
            foreach (['mi', 'ma', 'aprob', 'aplaz12', 'aplaz3', 'eval_oficial', 'eval_extra', 'no_evaluado', 'asist_ref', 'maest_ref', 'aa', 'as', 'ae', 'ai', 'valor'] as $m) {
                foreach (['as', 'f'] as $s) {
                    $total["{$m}_{$s}"] = array_sum(array_column($seccion['filas'], "{$m}_{$s}"));
                }
            }
            $total['perm_as'] = $total['mi_as'] > 0 ? round(($total['ma_as'] / $total['mi_as']) * 100, 1) : 0;
            $total['perm_f'] = $total['mi_f'] > 0 ? round(($total['ma_f'] / $total['mi_f']) * 100, 1) : 0;
            $total['aprob_pct_as'] = $total['eval_oficial_as'] > 0 ? round(($total['aprob_as'] / $total['eval_oficial_as']) * 100, 1) : 0;
            $total['aprob_pct_f'] = $total['eval_oficial_f'] > 0 ? round(($total['aprob_f'] / $total['eval_oficial_f']) * 100, 1) : 0;
            $secciones[$nombre]['total'] = $total;
        }

        return [
            'secciones' => array_values($secciones),
            'corte' => $corte,
            'modalidad' => $modalidadId ? Modalidad::find($modalidadId)?->nombre : null,
        ];
    }

    public function estadisticasFechas(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->subDays(7)->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $modalidadId = $filtros['modalidad_id'] ?? null;

        $asistencias = AsistenciaAula::with(['matricula.aula'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('matricula', function ($q) use ($anio, $modalidadId) {
                $q->where('anio_escolar_id', $anio?->id)
                    ->where('estado', 'activo');
                if ($modalidadId) {
                    $q->whereHas('aula', fn ($q2) => $q2->where('modalidad_id', $modalidadId));
                }
            })
            ->get();

        $agrupado = $asistencias->groupBy('fecha');
        $filas = [];
        $totalGeneral = 0;
        $presentesGeneral = 0;

        foreach ($agrupado as $fecha => $registros) {
            $total = $registros->count();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();
            $ausentes = $total - $presentes - $justificadas;
            $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;

            $totalGeneral += $total;
            $presentesGeneral += $presentes;

            $filas[] = [
                'fecha' => $fecha,
                'total' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $porcentaje,
            ];
        }

        return [
            'anio' => $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'promedio_rango' => $totalGeneral > 0 ? round(($presentesGeneral / $totalGeneral) * 100, 1) : 0,
            'filas' => collect($filas)->sortByDesc('fecha')->values()->all(),
        ];
    }

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
            if ($notasAlumno->isEmpty()) {
                continue;
            }

            $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
            if ($reprobadas === 0) {
                $aprobadosTodas++;
            } elseif ($reprobadas === 1) {
                $aplazados1++;
            } elseif ($reprobadas === 2) {
                $aplazados2++;
            } else {
                $aplazados3++;
            }
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

    public function certificadoNotas(
        int $alumnoId,
        ?int $anioId = null,
        ?int $gradoId = null,
        int $limiteGrados = self::CERTIFICADO_LIMITE_GRADOS
    ): array {
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
            return array_merge($vacio, ['alumno' => $alumno, 'anio' => $anio]);
        }

        $limiteGrados = max(1, $limiteGrados);
        $cadena = [$gradoBase];
        $actual = $gradoBase;

        for ($i = 1; $i < $limiteGrados; $i++) {
            $anterior = Grado::where('modalidad_id', $actual->modalidad_id)
                ->where('id', '<', $actual->id)
                ->orderByDesc('id')
                ->first();
            if (!$anterior) {
                break;
            }
            array_unshift($cadena, $anterior);
            $actual = $anterior;
        }

        $escala = IndicadorLogro::where('modalidad_id', $gradoBase->modalidad_id)
            ->pluck('nombre', 'codigo');

        $grados = [];
        $nombresAsignaturas = [];

        foreach ($cadena as $grado) {
            $matricula = Matricula::with(['aula.grado.modalidad', 'anioEscolar'])
                ->where('alumno_id', $alumno->id)
                ->when($grado->id === $gradoBase->id && $anio, fn ($q) => $q->where('anio_escolar_id', $anio->id))
                ->whereHas('aula', fn ($q) => $q->where('grado_id', $grado->id))
                ->latest('id')
                ->first();

            $notas = [];
            $finales = [];

            if ($matricula) {
                $asignaciones = AulaAsignaturaDocente::with('asignatura')
                    ->where('aula_id', $matricula->aula_id)
                    ->where('anio_escolar_id', $matricula->anio_escolar_id)
                    ->get()
                    ->sortBy('asignatura.nombre');

                foreach ($asignaciones as $asignacion) {
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

            $promedio = $finales !== [] ? round(array_sum($finales) / count($finales), 2) : null;
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

        $nombresAsignaturas = array_values(array_unique($nombresAsignaturas));
        sort($nombresAsignaturas);

        return [
            'alumno' => $alumno,
            'anio' => $anio,
            'matricula' => $matriculaBase ?? ($grados !== [] ? ($grados[array_key_last($grados)]['matricula']) : null),
            'gradoSeleccionado' => $gradoBase,
            'grados' => $grados,
            'asignaturas' => array_map(fn ($nombre) => ['nombre' => $nombre], $nombresAsignaturas),
            'limiteGrados' => $limiteGrados,
        ];
    }

    public function resumenIngresoNotas(?int $anioId): array
    {
        $asignacionesQuery = AulaAsignaturaDocente::where('anio_escolar_id', $anioId);
        $totalAsignaciones = $asignacionesQuery->count();
        $asignacionesIds = $asignacionesQuery->pluck('id');

        $totalEsperadas = 0;
        $aulasConMaterias = AulaAsignaturaDocente::selectRaw('aula_id, count(*) as materias')
            ->where('anio_escolar_id', $anioId)
            ->groupBy('aula_id')
            ->get();

        $aulasIds = $aulasConMaterias->pluck('aula_id');
        $matriculasPorAula = Matricula::selectRaw('aula_id, count(*) as alumnos')
            ->where('anio_escolar_id', $anioId)
            ->where('estado', 'activo')
            ->whereIn('aula_id', $aulasIds)
            ->groupBy('aula_id')
            ->get()
            ->keyBy('aula_id');

        foreach ($aulasConMaterias as $aulaMateria) {
            $alumnosEnAula = (int) ($matriculasPorAula->get($aulaMateria->aula_id)?->alumnos ?? 0);
            $totalEsperadas += ($alumnosEnAula * (int) $aulaMateria->materias);
        }

        $totalRegistradas = Nota::whereIn('aula_asignatura_docente_id', $asignacionesIds)
            ->get(['matricula_id', 'aula_asignatura_docente_id'])
            ->unique(fn ($nota) => $nota->matricula_id . '-' . $nota->aula_asignatura_docente_id)
            ->count();

        $totalPendientes = max(0, $totalEsperadas - $totalRegistradas);

        return [
            'asignaciones' => $totalAsignaciones,
            'total_notas_esperadas' => $totalEsperadas,
            'notas_registradas' => $totalRegistradas,
            'notas_pendientes' => $totalPendientes,
            'porcentaje' => $totalEsperadas > 0 ? round(($totalRegistradas / $totalEsperadas) * 100, 1) : 0,
        ];
    }

    public function asistenciaSeccionRango(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->subDays(7)->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $aulaId = $filtros['aula_id'] ?? null;

        $aula = null;
        $filas = [];

        if ($aulaId) {
            $aula = Aula::with('grado')->find($aulaId);
            $matriculas = Matricula::with('alumno')
                ->where('anio_escolar_id', $anio?->id)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

            $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get()
                ->groupBy('matricula_id');

            foreach ($matriculas as $matricula) {
                $registrosAlumno = $asistencias->get($matricula->id, collect());
                $presentes = $registrosAlumno->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
                $ausentes = $registrosAlumno->whereIn('estado_asistencia', ['Ausencia', 'Ausencia Injustificada'])->count();
                $justificadas = $registrosAlumno->where('estado_asistencia', 'Ausencia Justificada')->count();
                $totalClases = $registrosAlumno->count();

                $filas[] = [
                    'estudiante' => $matricula->alumno->nombre_completo,
                    'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                    'justificadas' => $justificadas,
                    'total' => $totalClases,
                    'porcentaje' => $totalClases > 0 ? round(($presentes / $totalClases) * 100, 1) : 0,
                ];
            }
        }

        return [
            'anio' => $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'aula' => $aula,
            'filas' => $filas,
        ];
    }

    public function asistenciaEstudiante(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->subMonth()->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $aulaId = $filtros['aula_id'] ?? null;
        $busqueda = trim((string) ($filtros['buscar'] ?? ''));

        $matriculas = Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when($aulaId, fn ($q) => $q->where('aula_id', $aulaId))
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $termino = '%' . mb_strtolower($busqueda, 'UTF-8') . '%';
                $q->whereHas('alumno', fn ($q2) => $q2->whereRaw('LOWER(nombre_completo) LIKE ?', [$termino]));
            })
            ->get()
            ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

        $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->groupBy('matricula_id');

        $filas = [];
        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            $registros = $asistencias->get($matricula->id, collect());
            $total = $registros->count();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $ausentes = $registros->whereIn('estado_asistencia', ['Ausencia', 'Ausencia Injustificada'])->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();

            $filas[] = [
                'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                'estudiante' => $alumno->nombre_completo,
                'aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                'registros' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }

        return [
            'anio' => $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'filas' => $filas,
        ];
    }

    public function rendimientoMinedDetallado(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $corteId = $filtros['corte_evaluativo_id'] ?? CorteEvaluativo::first()?->id;
        $tipo = $filtros['tipo'] ?? 'aprobados';

        $matriculas = Matricula::with(['alumno', 'aula.grado', 'aula.modalidad'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->get();

        $notasAgrupadas = Nota::with('aulaAsignaturaDocente.asignatura')
            ->where('corte_evaluativo_id', $corteId)
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->get()
            ->groupBy('matricula_id');

        $filas = [];
        $contador = 1;

        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            $aula = $matricula->aula;
            $notasAlumno = $notasAgrupadas->get($matricula->id, collect());
            $reprobadas = collect();

            foreach ($notasAlumno as $nota) {
                $nombreAsignatura = $nota->aulaAsignaturaDocente->asignatura->nombre ?? '';
                if (in_array($nombreAsignatura, ['TIC', 'Biblia', 'Informática', 'Computación'], true)) {
                    continue;
                }

                if (!is_null($nota->nota_cuantitativa) && (float) $nota->nota_cuantitativa < 60) {
                    $reprobadas->push($nombreAsignatura);
                }
            }

            $cantReprobadas = $reprobadas->count();
            $incluir = ($tipo === 'aprobados' && $cantReprobadas === 0)
                || ($tipo === '1_2' && $cantReprobadas >= 1 && $cantReprobadas <= 2)
                || ($tipo === '3_mas' && $cantReprobadas >= 3);

            if ($incluir) {
                $filas[] = [
                    'n' => $contador++,
                    'modalidad' => $aula->modalidad->nombre ?? 'Secundaria Regular',
                    'nivel' => $aula->grado->nombre ?? '11mo',
                    'turno' => ucfirst((string) ($aula->turno ?? 'Matutino')),
                    'codigo' => $alumno->codigo_unico_persona ?? 'S/C',
                    'nombre_completo' => $alumno->nombre_completo,
                    'sexo' => strtoupper((string) ($alumno->sexo ?? 'M')),
                    'asignaturas' => $reprobadas->implode(', '),
                ];
            }
        }

        return [
            'anio' => $anio,
            'tipo' => $tipo,
            'filas' => $filas,
        ];
    }

    public function estudiantes(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);
        $anioId = $anio?->id;

        $matriculas = Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anioId)
            ->get();

        $matriculados = $matriculas->where('estado', 'activo')->count();
        $retirados = $matriculas->where('estado', 'retirado')->count();
        $totalAlumnos = Alumno::count();

        $incompletos = [];
        foreach ($matriculas->where('estado', 'activo') as $matricula) {
            $alumno = $matricula->alumno;
            $faltantes = [];

            if (empty($alumno->direccion_domiciliar)) {
                $faltantes[] = 'Dirección';
            }
            if (empty($alumno->fecha_nacimiento)) {
                $faltantes[] = 'F. Nacimiento';
            }
            if (empty($alumno->madre_nombre_completo)) {
                $faltantes[] = 'Datos Madre';
            }
            if (empty($alumno->madre_telefono)) {
                $faltantes[] = 'Tel. Madre';
            }
            if (empty($alumno->tutor_nombre_completo)) {
                $faltantes[] = 'Datos Tutor';
            }

            if ($faltantes !== []) {
                $telefonoContacto = $alumno->madre_telefono
                    ?? $alumno->padre_telefono
                    ?? $alumno->tutor_telefono
                    ?? 'Sin número registrado';

                $incompletos[] = [
                    'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                    'alumno' => $alumno->nombre_completo,
                    'grado_aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                    'faltantes' => implode(', ', $faltantes),
                    'telefono' => $telefonoContacto,
                ];
            }
        }

        $incompletos = collect($incompletos)->sortBy('alumno')->values()->all();

        return [
            'anio' => $anio,
            'total_alumnos' => $totalAlumnos,
            'matriculados' => $matriculados,
            'retirados' => $retirados,
            'expedientes_incompletos' => count($incompletos),
            'lista_incompletos' => $incompletos,
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

    public function padres(array $filtros = []): array
    {
        $anioId = $filtros['anio_escolar_id'] ?? null;
        $aulaId = $filtros['aula_id'] ?? null;
        $anio = $this->resolverAnio($anioId);

        $matriculas = Matricula::with(['alumno.usuario', 'aula.grado'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when($aulaId, fn ($q) => $q->where('aula_id', $aulaId))
            ->get()
            ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

        $conTelefono = 0;
        $conEmail = 0;
        $filas = [];

        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            $tieneTel = !empty($alumno->madre_telefono) || !empty($alumno->padre_telefono) || !empty($alumno->tutor_telefono);
            $tieneEmail = $alumno->usuario && !empty($alumno->usuario->email);

            if ($tieneTel) {
                $conTelefono++;
            }
            if ($tieneEmail) {
                $conEmail++;
            }

            $filas[] = [
                'estudiante' => $alumno->nombre_completo,
                'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                'grado_aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                'madre' => $alumno->madre_nombre_completo
                    ? e($alumno->madre_nombre_completo) . '<br><span class="text-xs font-bold text-slate-500">' . e($alumno->madre_telefono ?? 'Sin tel') . '</span>'
                    : '-',
                'padre' => $alumno->padre_nombre_completo
                    ? e($alumno->padre_nombre_completo) . '<br><span class="text-xs font-bold text-slate-500">' . e($alumno->padre_telefono ?? 'Sin tel') . '</span>'
                    : '-',
                'tutor' => $alumno->tutor_nombre_completo
                    ? e($alumno->tutor_nombre_completo) . '<br><span class="text-xs font-bold text-slate-500">' . e($alumno->tutor_telefono ?? 'Sin tel') . '</span>'
                    : '-',
                'autorizado' => $alumno->autorizado_retirar_nombre
                    ? e($alumno->autorizado_retirar_nombre) . '<br><span class="text-xs font-bold text-slate-500">' . e($alumno->autorizado_retirar_telefono ?? 'Sin tel') . '</span>'
                    : '-',
            ];
        }

        $total = $matriculas->count();

        return [
            'anio' => $anio,
            'aula' => $aulaId ? Aula::with('grado')->find($aulaId) : null,
            'total' => $total,
            'con_telefono' => $conTelefono,
            'con_email' => $conEmail,
            'porcentaje_adopcion' => $total > 0 ? round(($conTelefono / $total) * 100, 1) : 0,
            'filas' => $filas,
        ];
    }
}