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
use Illuminate\Support\Facades\DB;

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

        if (!empty($filtros['aula_id'])) {
            $query->where('aula_id', $filtros['aula_id']);
        }
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

    /**
     * Auditoría detallada: Asistencia exacta de un aula en un día en específico.
     */
    public function asistenciaPorSeccion(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $fecha = $filtros['fecha'] ?? now()->toDateString();
        $aulaId = $filtros['aula_id'] ?? null;

        $aula = null;
        $filas = [];
        $totales = ['presentes' => 0, 'ausentes' => 0, 'justificadas' => 0, 'sin_registrar' => 0, 'total' => 0];

        if ($aulaId) {
            $aula = \App\Models\Aula::with('grado')->find($aulaId);
            
            // 1. Obtenemos solo a los alumnos activos de esa aula
            $matriculas = \App\Models\Matricula::with('alumno')
                ->where('anio_escolar_id', $anio?->id)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn($m) => $m->alumno->nombre_completo ?? '');

            // 2. Extraemos los registros de asistencia de ese día
            $asistencias = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->where('fecha', $fecha)
                ->get()
                ->keyBy('matricula_id');

            $totales['total'] = $matriculas->count();

            // 3. Cruzamos la lista de estudiantes con su asistencia
            foreach ($matriculas as $matricula) {
                $registro = $asistencias->get($matricula->id);
                // Si no hay registro, el maestro no pasó lista para ese alumno
                $estado = $registro ? $registro->estado_asistencia : 'Sin Registrar';

                if ($estado === 'Presente' || $estado === 'Actividad Institucional') {
                    $totales['presentes']++;
                    $colorTema = 'text-emerald-700 bg-emerald-50 border-emerald-200';
                } elseif ($estado === 'Ausencia Justificada') {
                    $totales['justificadas']++;
                    $colorTema = 'text-amber-700 bg-amber-50 border-amber-200';
                } elseif ($estado === 'Ausencia') {
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
                    'observacion' => $registro && $registro->observaciones ? $registro->observaciones : '-',
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

    // =========================================================================
    // CONTROL DE INGRESO DE NOTAS
    // =========================================================================

    /**
     * Control de notas (ingresadas o pendientes) por asignación.
     */
    public function controlNotas(array $filtros): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        
        $corteId = $filtros['corte_evaluativo_id'] ?? null;
        $tipo = $filtros['tipo'] ?? 'todos'; // 'todos' | 'pendientes' | 'ingresadas'

        // 1. Obtener las asignaciones con sus relaciones básicas (1 sola consulta)
        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        $asignacionIds = $asignaciones->pluck('id');

        if ($asignacionIds->isEmpty()) {
            return [];
        }

        // 2. Conteo REAL de notas registradas agrupadas por asignación (1 sola consulta rápida)
        $notasAgrupadas = \App\Models\Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->select('aula_asignatura_docente_id', \Illuminate\Support\Facades\DB::raw('count(distinct matricula_id) as registradas'))
            ->groupBy('aula_asignatura_docente_id')
            ->pluck('registradas', 'aula_asignatura_docente_id');

        // 3. Obtener el mapa de matrículas activas por cada aula (1 sola consulta)
        $aulaIds = $asignaciones->pluck('aula_id')->unique();
        $matriculasPorAula = \App\Models\Matricula::whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->select('aula_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('aula_id')
            ->pluck('total', 'aula_id');

        // 4. Obtener el estado de los cortes cerrados en bloque (1 sola consulta)
        $cortesCerrados = \App\Models\CorteCerrado::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->where('bloqueado', true)
            ->pluck('aula_asignatura_docente_id')
            ->flip();

        $filas = [];
        foreach ($asignaciones as $asignacion) {
            $total = $matriculasPorAula->get($asignacion->aula_id, 0);
            $conNota = $notasAgrupadas->get($asignacion->id, 0);
            $pendientes = max(0, $total - $conNota);
            $cerrado = $cortesCerrados->has($asignacion->id);

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

        return collect($filas)->sort(function ($a, $b) {
            $cmpGrado = strcmp($a['grado'], $b['grado']);
            if ($cmpGrado !== 0) return $cmpGrado;

            $cmpAula = strcmp($a['aula'], $b['aula']);
            if ($cmpAula !== 0) return $cmpAula;

            return strcmp($a['asignatura'], $b['asignatura']);
        })->values()->all();
    }

    /**
     * Notas globales por alumno: una fila por alumno, con una columna por asignatura.
     * Devuelve la lista de asignaturas (columnas) y los alumnos con su nota por asignatura.
     * Si se filtra por corte, muestra la nota de ese corte; si no, el promedio final de la asignatura.
     */
    /**
     * Sábana de notas (Notas Globales).
     * Obligatorio filtrar por Aula. Devuelve estudiantes en filas y materias en columnas.
     */
    public function notasGlobalesPorAlumno(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $aulaId = $filtros['aula_id'] ?? null;
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        // Asignaciones del año (y filtros opcionales de grado/aula/asignatura/modalidad/docente).
        // Híbrido merge feauture/no: se conservan los filtros flexibles de fase 8 y el
        // diseño de sábana por aula de la otra rama (excluye TIC/Biblia/Informática, rows con cup/promedio/reprobadas).
        $asignaciones = \App\Models\AulaAsignaturaDocente::with('asignatura')
            ->where('anio_escolar_id', $anio?->id)
            ->when(!empty($filtros['aula_id']), fn ($q) => $q->where('aula_id', $filtros['aula_id']))
            ->when(!empty($filtros['grado_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->when(!empty($filtros['modalidad_id']), fn ($q) => $q->whereHas('aula', fn ($q2) => $q2->where('modalidad_id', $filtros['modalidad_id'])))
            ->when(!empty($filtros['asignatura_id']), fn ($q) => $q->where('asignatura_id', $filtros['asignatura_id']))
            ->when(!empty($filtros['docente_id']), fn ($q) => $q->where('docente_id', $filtros['docente_id']))
            ->get();

        $aula = $aulaId ? \App\Models\Aula::with('grado')->find($aulaId) : null;

        $asignaturas = $asignaciones->pluck('asignatura')->unique('id')
            ->reject(function ($asig) {
                // Exclusión intencional de la rama feauture/no (TIC/Biblia/Informática), corregida:
                // coincidencia exacta (el substring 'TIC' mataba 'Matemática', 'Estadística', etc.).
                $nombreNormalizado = str_replace(
                    ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'],
                    ['A', 'E', 'I', 'O', 'U', 'U'],
                    mb_strtoupper(trim($asig->nombre))
                );
                return in_array($nombreNormalizado, ['TIC', 'BIBLIA', 'INFORMATICA'], true);
            })
            ->sortBy('nombre')->values();

        // 2. Obtener matrículas activas de las aulas involucradas (respeta los filtros) ordenadas alfabéticamente
        $aulasInvolucradas = $asignaciones->pluck('aula_id')->unique();
        $matriculas = \App\Models\Matricula::with('alumno')
            ->whereIn('aula_id', $aulasInvolucradas)
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->get()
            ->sortBy(fn($m) => $m->alumno->nombre_completo ?? '');

        // 3. Obtener todas las notas de estos alumnos en bloque
        $notas = \App\Models\Nota::whereIn('matricula_id', $matriculas->pluck('id'))
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
                // Buscar si el alumno tiene nota en la asignación correspondiente a esta materia
                $notasDeAsignatura = $notasAlumno->filter(function ($n) use ($asignatura) {
                    return $n->aulaAsignaturaDocente->asignatura_id === $asignatura->id;
                });

                if ($notasDeAsignatura->isNotEmpty()) {
                    // Si no hay filtro de corte, hace el promedio anual de la materia; si hay corte, promedia las actividades de ese corte.
                    $notaFinal = round((float) $notasDeAsignatura->avg('nota_cuantitativa'));
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

    /**
     * Notas pendientes: matriculas activas sin nota en una asignación y corte.
     */
    public function notasPendientes(array $filtros): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        // 1. Obtener asignaciones filtradas (1 consulta rápida)
        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        if ($asignaciones->isEmpty()) {
            return [];
        }

        // 2. Obtener matrículas activas de las aulas involucradas agrupadas por aula (1 consulta)
        $aulaIds = $asignaciones->pluck('aula_id')->unique();
        $matriculas = \App\Models\Matricula::with('alumno')
            ->whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->get()
            ->groupBy('aula_id');

        // 3. Obtener notas existentes agrupadas por asignación (1 consulta)
        $asignacionIds = $asignaciones->pluck('id');
        $notasExistentes = \App\Models\Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get(['matricula_id', 'aula_asignatura_docente_id'])
            ->groupBy('aula_asignatura_docente_id');

        $pendientes = [];

        foreach ($asignaciones as $asignacion) {
            $alumnosAula = $matriculas->get($asignacion->aula_id, []);
            
            // Extraer solo los IDs de matrícula que ya tienen nota en esta asignatura
            $conNotaIds = $notasExistentes->get($asignacion->id, collect())->pluck('matricula_id')->toArray();

            foreach ($alumnosAula as $matricula) {
                if (!in_array($matricula->id, $conNotaIds)) {
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

        // Ordenar alfabéticamente por docente para facilitar el cobro, luego asignatura y estudiante
        return collect($pendientes)->sortBy([
            ['docente', 'asc'],
            ['asignatura', 'asc'],
            ['estudiante', 'asc'],
        ])->values()->all();
    }

    // =========================================================================
    // ASISTENCIA (segmentada)
    // =========================================================================

    /**
     * Asistencia global: resumen por aula para una fecha.
     */
    /**
     * Asistencia global: resumen consolidado diario por aula para una fecha específica.
     */
    public function asistenciaGlobal(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        // Por defecto usamos la fecha de hoy si no se envía ninguna
        $fecha = $filtros['fecha'] ?? now()->toDateString();

        // 1. Obtener aulas (1 sola consulta)
        $aulas = \App\Models\Aula::with(['grado', 'modalidad'])
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

        // 2. Obtener matrículas activas de estas aulas (1 sola consulta)
        $aulaIds = $aulas->pluck('id');
        $matriculas = \App\Models\Matricula::whereIn('aula_id', $aulaIds)
            ->where('estado', 'activo')
            ->get(['id', 'aula_id']);
        
        $matriculasPorAula = $matriculas->groupBy('aula_id');

        // 3. Obtener los registros de asistencia de esa fecha (1 sola consulta)
        $asistencias = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
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
                // Si el maestro no pasó lista, se asume como Ausencia para la métrica de control
                $estado = $asistencias->get($m->id)?->estado_asistencia;
                
                if ($estado === 'Presente' || $estado === 'Actividad Institucional') {
                    $presentes++;
                } elseif ($estado === 'Ausencia Justificada') {
                    $justificadas++;
                } else {
                    $ausentes++;
                }
            }

            $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;

            $reporte[] = [
                'aula' => ($aula->grado->nombre ?? '') . ' - ' . $aula->nombre,
                'turno' => $aula->turno ?? 'No definido',
                'modalidad' => $aula->modalidad->nombre ?? 'Regular',
                'total' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $porcentaje,
            ];
        }

        return [
            'fecha' => $fecha,
            'filas' => collect($reporte)->sortByDesc('porcentaje')->values()->all()
        ];
    }

    /**
     * Auditoría de Avance Programático (MINED / Interno) por Docente o por Aula.
     */
    public function auditoriaAvanceContenido(array $filtros, string $perspectiva = 'docente'): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        $asignaciones = $query->get();

        $reporte = [];

        foreach ($asignaciones as $asignacion) {
            // Buscamos el avance registrado para esta asignación en el año activo
            $avanceModel = \App\Models\AvanceContenido::where('aula_asignatura_docente_id', $asignacion->id)->latest()->first();
            
            $porcentaje = $avanceModel ? (float) $avanceModel->porcentaje_avance : 0.0;
            $unidadesImpartidas = $avanceModel->unidades_impartidas ?? 'No registrado';

            if ($perspectiva === 'docente') {
                $reporte[] = [
                    'docente' => $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar',
                    'asignatura' => $asignacion->asignatura->nombre,
                    'grado' => $asignacion->aula->grado->nombre . ' - Sección "' . $asignacion->aula->nombre . '"',
                    'detalle' => $unidadesImpartidas,
                    'porcentaje' => $porcentaje,
                ];
            } else {
                // Perspectiva por Aula / Grado
                $reporte[] = [
                    'grado' => $asignacion->aula->grado->nombre . ' - Sección "' . $asignacion->aula->nombre . '"',
                    'asignatura' => $asignacion->asignatura->nombre,
                    'docente' => $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar',
                    'detalle' => $unidadesImpartidas,
                    'porcentaje' => $porcentaje,
                ];
            }
        }

        // Ordenar según la perspectiva elegida
        $coleccion = collect($reporte);
        if ($perspectiva === 'docente') {
            return $coleccion->sortBy('docente')->values()->all();
        } else {
            return $coleccion->sortBy('grado')->values()->all();
        }
    }

    /**
     * Estadísticas agregadas de asistencia por aula (rango de fechas).
     */
    public function estadisticasAsistencia(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        
        // Por defecto: últimos 7 días hasta hoy
        $fechaInicio = $filtros['fecha_inicio'] ?? now()->subDays(7)->toDateString();
        $fechaFin = $filtros['fecha_fin'] ?? now()->toDateString();
        $modalidadId = $filtros['modalidad_id'] ?? null;

        // 1. Extraer todo en una sola consulta optimizada (diseño de la rama feauture/no)
        $query = \App\Models\AsistenciaAula::with(['matricula.aula'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('matricula', function($q) use ($anio, $modalidadId) {
                $q->where('anio_escolar_id', $anio?->id)
                  ->where('estado', 'activo');
                  
                if ($modalidadId) {
                    $q->whereHas('aula', fn($q2) => $q2->where('modalidad_id', $modalidadId));
                }
            });

        $asistencias = $query->get();
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
                'porcentaje' => $porcentaje
            ];
        }

        $filas = collect($filas)->sortByDesc('fecha')->values()->all();
        $promedioRango = $totalGeneral > 0 ? round(($presentesGeneral / $totalGeneral) * 100, 1) : 0;

        return [
            'anio' => $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'promedio_rango' => $promedioRango,
            'filas' => $filas
        ];
    }

    /**
     * Estadísticas de asistencia por estudiante (en un aula y rango).
     */
    public function estadisticasPorEstudiante(array $filtros): array
    {
        $aulaId = $filtros['aula_id'] ?? null;
        $inicio = $filtros['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fin = $filtros['fecha_fin'] ?? now()->toDateString();

        $matriculas = Matricula::with(['alumno', 'aula'])
            ->when($aulaId, fn ($q) => $q->where('aula_id', $aulaId))
            ->where('estado', 'activo')->get();

        // Diseño de la rama feauture/no: filas con cup/estudiante/aula/registros para su vista.
        $filas = [];
        foreach ($matriculas as $m) {
            $registros = AsistenciaAula::where('matricula_id', $m->id)->whereBetween('fecha', [$inicio, $fin])->get();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $ausentes = $registros->where('estado_asistencia', 'Ausencia Injustificada')->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();
            $total = $registros->count();

            $filas[] = [
                'cup' => $m->alumno->codigo_unico_persona ?? 'S/C',
                'estudiante' => $m->alumno->nombre_completo,
                'aula' => $m->aula->nombre ?? '',
                'registros' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }

        return [
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'filas' => $filas,
        ];
    }

    // =========================================================================
    // RENDIMIENTO ACADÉMICO
    // =========================================================================

    /**
     * Notas por asignatura: promedio por asignación (aula-asignatura).
     */
    /**
     * Rendimiento por Docente y Asignatura (Auditoría de Desempeño y Dificultad).
     */
    public function notasPorAsignatura(array $filtros): array
    {
        [$query, $anio] = $this->queryAsignaciones($filtros);
        $corteId = $filtros['corte_evaluativo_id'] ?? null;

        // 1. Obtener asignaciones
        $asignaciones = $query->with(['aula.grado', 'asignatura', 'docente.usuario'])->get();
        $asignacionIds = $asignaciones->pluck('id');

        if ($asignacionIds->isEmpty()) {
            return [];
        }

        // 2. Obtener todas las notas de un solo golpe para evitar N+1
        $notas = \App\Models\Nota::whereIn('aula_asignatura_docente_id', $asignacionIds)
            ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', $corteId))
            ->get(['aula_asignatura_docente_id', 'nota_cuantitativa'])
            ->groupBy('aula_asignatura_docente_id');

        $agrupado = [];

        // 3. Consolidar la data agrupando por Docente y Asignatura
        foreach ($asignaciones as $asignacion) {
            $docenteNombre = $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar';
            $asignaturaNombre = $asignacion->asignatura->nombre;
            $gradoSeccion = ($asignacion->aula->grado->nombre ?? '') . ' "' . ($asignacion->aula->nombre ?? '') . '"';
            
            // Llave única para agrupar las materias de un mismo maestro
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

            if (!in_array($gradoSeccion, $agrupado[$key]['aulas'])) {
                $agrupado[$key]['aulas'][] = $gradoSeccion;
            }

            $agrupado[$key]['evaluados'] += $notasAsignacion->count();
            $agrupado[$key]['aprobados'] += $notasAsignacion->where('nota_cuantitativa', '>=', 60)->count();
            $agrupado[$key]['reprobados'] += $notasAsignacion->where('nota_cuantitativa', '<', 60)->count();
            $agrupado[$key]['suma_notas'] += $notasAsignacion->sum('nota_cuantitativa');
        }

        // 4. Calcular promedios finales y tasas
        $reporte = [];
        foreach ($agrupado as $item) {
            $evaluados = $item['evaluados'];
            $promedio = $evaluados > 0 ? round($item['suma_notas'] / $evaluados, 2) : 0;
            $tasaRiesgo = $evaluados > 0 ? round(($item['reprobados'] / $evaluados) * 100, 1) : 0;
            
            $reporte[] = [
                'docente' => $item['docente'],
                'asignatura' => $item['asignatura'],
                'aulas' => implode(', ', $item['aulas']), // Ej: "1ro A, 1ro B, 2do A"
                'evaluados' => $evaluados,
                'aprobados' => $item['aprobados'],
                'reprobados' => $item['reprobados'],
                'tasa_riesgo' => $tasaRiesgo,
                'promedio' => $promedio,
            ];
        }

        // Ordenar alfabéticamente por Docente
        return collect($reporte)->sortBy([
            ['docente', 'asc'],
            ['asignatura', 'asc']
        ])->values()->all();
    }

    /**
     * Rendimiento académico por corte — Formato oficial MINED
     * "FORMATO DE REND. GENERAL Y ESCALA POR ESCUELA III CORTE 2026".
     *
     * Una tabla por modalidad; cada métrica partida en AS (ambos sexos,
     * incluye femenino) y F (solo femenino). Los desgloses por sexo se filtran
     * sobre las colecciones Eloquent ya cargadas en memoria (volumen por grado
     * es de decenas; no amerita conteos SQL separados). La clasificación por
     * nº de materias reprobadas (<60) y la escala cualitativa reutilizan
     * NotaService (escala vigente 2026: AA/AS/AE/AI).
     *
     * Supuestos documentados:
     * - % DE PERM  = MA / MI        (permanencia de la matrícula).
     * - % DE APROB = APROB / EVALUADO PERIODO OFICIAL.
     * - EVALUADO PERIODO OFICIAL = MA con al menos una nota en el corte.
     * - EVALUADO PERIODO EXTRA   = MA con examen de reparación en el rango
     *                              del corte (o cualquiera del año si no hay corte).
     * - NO EVALUADO = MA − EVALUADO PERIODO OFICIAL.
     * - ASIST REFORZ / MAEST INT REF = 0: el sistema no tiene módulo de
     *   reforzamiento interdocente (celdas en blanco como el formato).
     * - ESCALA/VALOR = total de evaluados con escala (AA + AS + AE + AI).
     */
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

            // MI: TODAS las matrículas del año/grado (incluye retirados)
            // MA: solo matrículas ACTIVAS
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

            // Periodo extra: matrículas con examen de reparación (rango del corte o del año)
            $reparacionIds = \App\Models\ExamenReparacion::whereIn('matricula_id', $matriculaIds)
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

                // Sin notas en el corte → NO EVALUADO / SIN EVALUAC
                if (!$notasAlumno || $notasAlumno->isEmpty()) {
                    $fila['no_evaluado_as']++;
                    if ($esF) $fila['no_evaluado_f']++;
                    continue;
                }

                $fila['eval_oficial_as']++;
                if ($esF) $fila['eval_oficial_f']++;

                // APROB / APLAZ por nº de asignaturas reprobadas (< 60)
                $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();
                if ($reprobadas === 0) {
                    $fila['aprob_as']++;
                    if ($esF) $fila['aprob_f']++;
                } elseif ($reprobadas <= 2) {
                    $fila['aplaz12_as']++;
                    if ($esF) $fila['aplaz12_f']++;
                } else {
                    $fila['aplaz3_as']++;
                    if ($esF) $fila['aplaz3_f']++;
                }

                // Escala: banda cualitativa del promedio del corte
                $banda = $this->notaService->calcularIndicadorLogro((int) round((float) $notasAlumno->avg('nota_cuantitativa')));
                $clave = match ($banda) {
                    'AA' => 'aa', 'AS' => 'as', 'AE' => 'ae', 'AI' => 'ai', default => null,
                };
                if ($clave) {
                    $fila["{$clave}_as"]++;
                    if ($esF) $fila["{$clave}_f"]++;
                }
            }

            // Porcentajes por fila (guarda de división por cero)
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

        // Fila TOTAL por modalidad (sumas; porcentajes recalculados desde totales)
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

    /**
     * Estadísticas de asistencia agrupadas por fecha en un rango específico.
     */
    public function estadisticasFechas(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        
        // Por defecto: últimos 7 días hasta hoy
        $fechaInicio =$filtros['fecha_inicio'] ?? now()->subDays(7)->toDateString();
        $fechaFin =$filtros['fecha_fin'] ?? now()->toDateString();
        $modalidadId =$filtros['modalidad_id'] ?? null;

        // 1. Obtener todas las asistencias en el rango de fechas con su matrícula
        $query = \App\Models\AsistenciaAula::with(['matricula.aula'])
            ->whereBetween('fecha', [$fechaInicio,$fechaFin])
            ->whereHas('matricula', function($q) use ($anio,$modalidadId) {
                $q->where('anio_escolar_id',$anio?->id)
                  ->where('estado', 'activo');
                  
                if ($modalidadId) {
                    $q->whereHas('aula', fn($q2) => $q2->where('modalidad_id',$modalidadId));
                }
            });

        $asistencias =$query->get();

        // 2. Agrupar los registros por día
        $agrupado =$asistencias->groupBy('fecha');
        
        $filas = [];$totalGeneral = 0;
        $presentesGeneral = 0;

        foreach ($agrupado as $fecha =>$registros) {
            $total =$registros->count();
            $presentes =$registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $justificadas =$registros->where('estado_asistencia', 'Ausencia Justificada')->count();
            $ausentes =$total - $presentes -$justificadas;
            
            $porcentaje =$total > 0 ? round(($presentes / $total) * 100, 1) : 0;
            
            $totalGeneral +=$total;
            $presentesGeneral +=$presentes;

            $filas[] = [
                'fecha' => $fecha,
                'total' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje' => $porcentaje
            ];
        }

        // Ordenamos para que las fechas más recientes salgan arriba
        $filas = collect($filas)->sortByDesc('fecha')->values()->all();

        $promedioRango =$totalGeneral > 0 ? round(($presentesGeneral / $totalGeneral) * 100, 1) : 0;

        return [
            'anio' => $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'promedio_rango' => $promedioRango,
            'filas' => $filas
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
     * finales (NF cuantitativa + equivalencia cualitativa AA/AS/AE/AI) del
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
        // 1. Obtener todas las asignaciones activas del año actual
        $asignacionesQuery = AulaAsignaturaDocente::where('anio_escolar_id', $anioId);
        $totalAsignaciones = $asignacionesQuery->count();
        $asignacionesIds = $asignacionesQuery->pluck('id');

        // 2. Calcular cuántas notas "deberían" existir.
        // Multiplicamos la cantidad de alumnos matriculados en cada aula por la cantidad de materias de esa aula.
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
            $alumnosEnAula = $matriculasPorAula->get($aulaMateria->aula_id)?->alumnos ?? 0;
            $totalEsperadas += ($alumnosEnAula * $aulaMateria->materias);
        }

        // 3. Contar cuántas notas reales únicas (por alumno y materia) existen en la base de datos
        // Conteo de pares únicos en PHP: concat() de SQL no existe en SQLite (tests) y en
        // PostgreSQL requierería casts; así es agnóstico al motor (base: nuestro trabajo).
        $totalRegistradas = Nota::whereIn('aula_asignatura_docente_id', $asignacionesIds)
            ->get(['matricula_id', 'aula_asignatura_docente_id'])
            ->unique(fn ($nota) => $nota->matricula_id.'-'.$nota->aula_asignatura_docente_id)
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
            $aula = \App\Models\Aula::with('grado')->find($aulaId);
            
            $matriculas = \App\Models\Matricula::with('alumno')
                ->where('anio_escolar_id', $anio?->id)
                ->where('aula_id', $aulaId)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn($m) => $m->alumno->nombre_completo ?? '');

            $asistencias = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get()
                ->groupBy('matricula_id');

            foreach ($matriculas as $matricula) {
                $registrosAlumno = $asistencias->get($matricula->id, collect());
                
                $presentes = $registrosAlumno->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
                $ausentes = $registrosAlumno->where('estado_asistencia', 'Ausencia')->count();
                $justificadas = $registrosAlumno->where('estado_asistencia', 'Ausencia Justificada')->count();
                $totalClases = $registrosAlumno->count();

                $porcentaje = $totalClases > 0 ? round(($presentes / $totalClases) * 100, 1) : 0;

                $filas[] = [
                    'estudiante' => $matricula->alumno->nombre_completo,
                    'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                    'justificadas' => $justificadas,
                    'total' => $totalClases,
                    'porcentaje' => $porcentaje,
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
        $busqueda = $filtros['buscar'] ?? null;

        // Obtener matrículas activas con filtros aplicados
        $query = \App\Models\Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when($aulaId, fn($q) => $q->where('aula_id', $aulaId))
            ->when($busqueda, fn($q) => $q->whereHas('alumno', fn($q2) => $q2->where('nombre_completo', 'ilike', "%{$busqueda}%")));

        $matriculas = $query->get()->sortBy(fn($m) => $m->alumno->nombre_completo ?? '');

        // Obtener asistencias en el rango
        $asistencias = \App\Models\AsistenciaAula::whereIn('matricula_id', $matriculas->pluck('id'))
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->groupBy('matricula_id');

        $filas = [];
        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            $registros = $asistencias->get($matricula->id, collect());

            $total = $registros->count();
            $presentes = $registros->whereIn('estado_asistencia', ['Presente', 'Actividad Institucional'])->count();
            $ausentes = $registros->where('estado_asistencia', 'Ausencia')->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();

            $porcentaje = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;

            $filas[] = [
                'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                'estudiante' => $alumno->nombre_completo,
                'aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                'registros' => $total,
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
            'filas' => $filas,
        ];
    }

    public function rendimientoMinedDetallado(array $filtros): array
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);
        $corteId = $filtros['corte_evaluativo_id'] ?? \App\Models\CorteEvaluativo::first()?->id;
        $tipo = $filtros['tipo'] ?? 'aprobados'; // aprobados, 1_2, 3_mas

        $matriculas = \App\Models\Matricula::with(['alumno', 'aula.grado', 'aula.modalidad'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo')
            ->when(!empty($filtros['grado_id']), fn($q) => $q->whereHas('aula', fn($q2) => $q2->where('grado_id', $filtros['grado_id'])))
            ->get();

        // Precargar todas las notas del corte con sus asignaciones y asignaturas en una sola consulta
        $notasAgrupadas = \App\Models\Nota::with('asignacionDocente.asignatura')
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
                $nombreAsignatura = $nota->asignacionDocente->asignatura->nombre ?? '';
                
                // Excluimos estrictamente materias institucionales (TIC, Biblia, Informática, Computación)
                if (in_array($nombreAsignatura, ['TIC', 'Biblia', 'Informática', 'Computación'])) {
                    continue;
                }

                if ($nota->calificacion < 60) {
                    $reprobadas->push($nombreAsignatura);
                }
            }

            $cantReprobadas = $reprobadas->count();

            $incluir = false;
            if ($tipo === 'aprobados' && $cantReprobadas === 0) {
                $incluir = true;
            } elseif ($tipo === '1_2' && $cantReprobadas >= 1 && $cantReprobadas <= 2) {
                $incluir = true;
            } elseif ($tipo === '3_mas' && $cantReprobadas >= 3) {
                $incluir = true;
            }

            if ($incluir) {
                $filas[] = [
                    'n' => $contador++,
                    'modalidad' => $aula->modalidad->nombre ?? 'Secundaria Regular',
                    'nivel' => $aula->grado->nombre ?? '11mo',
                    'turno' => ucfirst($aula->turno ?? 'Matutino'),
                    'codigo' => $alumno->codigo_unico_persona ?? 'S/C',
                    'nombre_completo' => $alumno->nombre_completo,
                    'sexo' => strtoupper($alumno->sexo ?? 'M'),
                    'asignaturas' => $reprobadas->implode(', ')
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

        // Obtener matrículas del año actual con sus relaciones
        $matriculas = \App\Models\Matricula::with(['alumno', 'aula.grado'])
            ->where('anio_escolar_id', $anioId)
            ->get();

        $matriculados = $matriculas->where('estado', 'activo')->count();
        $retirados = $matriculas->where('estado', 'retirado')->count();
        $totalAlumnos = \App\Models\Alumno::count();

        // Procesar la lista de expedientes incompletos (solo de alumnos activos)
        $incompletos = [];
        foreach ($matriculas->where('estado', 'activo') as $matricula) {
            $alumno = $matricula->alumno;
            $faltantes = [];

            if (empty($alumno->direccion_domiciliar)) $faltantes[] = 'Dirección';
            if (empty($alumno->fecha_nacimiento)) $faltantes[] = 'F. Nacimiento';
            if (empty($alumno->madre_nombre_completo)) $faltantes[] = 'Datos Madre';
            if (empty($alumno->madre_telefono)) $faltantes[] = 'Tel. Madre';
            if (empty($alumno->tutor_nombre_completo)) $faltantes[] = 'Datos Tutor';

            if (!empty($faltantes)) {
                // Rescatar cualquier teléfono disponible (Madre, Padre o Tutor)
                $telefonoContacto = $alumno->madre_telefono ?? $alumno->padre_telefono ?? $alumno->tutor_telefono ?? 'Sin número registrado';

                $incompletos[] = [
                    'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                    'alumno' => $alumno->nombre_completo,
                    'grado_aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                    'faltantes' => implode(', ', $faltantes),
                    'telefono' => $telefonoContacto,
                ];
            }
        }

        // Ordenar alfabéticamente la lista de incompletos
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

        // Buscar solo matrículas activas del ciclo, filtradas por aula si se selecciona una
        $query = \App\Models\Matricula::with(['alumno.usuario', 'aula.grado'])
            ->where('anio_escolar_id', $anio?->id)
            ->where('estado', 'activo');

        if ($aulaId) {
            $query->where('aula_id', $aulaId);
        }

        $matriculas = $query->get()->sortBy(fn($m) => $m->alumno->nombre_completo ?? '');

        $conTelefono = 0;
        $conEmail = 0;
        $filas = [];

        foreach ($matriculas as $matricula) {
            $alumno = $matricula->alumno;
            
            $tieneTel = !empty($alumno->madre_telefono) || !empty($alumno->padre_telefono) || !empty($alumno->tutor_telefono);
            // Si el alumno (o responsable) tiene un usuario asociado con email
            $tieneEmail = $alumno->usuario && !empty($alumno->usuario->email); 

            if ($tieneTel) $conTelefono++;
            if ($tieneEmail) $conEmail++;

            // Formateamos los contactos para que muestren "Nombre (Teléfono)"
            $filas[] = [
                'estudiante' => $alumno->nombre_completo,
                'cup' => $alumno->codigo_unico_persona ?? 'S/C',
                'grado_aula' => ($matricula->aula->grado->nombre ?? '') . ' - ' . ($matricula->aula->nombre ?? ''),
                'madre' => $alumno->madre_nombre_completo ? $alumno->madre_nombre_completo . '<br><span class="text-xs font-bold text-slate-500">' . ($alumno->madre_telefono ?? 'Sin tel') . '</span>' : '-',
                'padre' => $alumno->padre_nombre_completo ? $alumno->padre_nombre_completo . '<br><span class="text-xs font-bold text-slate-500">' . ($alumno->padre_telefono ?? 'Sin tel') . '</span>' : '-',
                'tutor' => $alumno->tutor_nombre_completo ? $alumno->tutor_nombre_completo . '<br><span class="text-xs font-bold text-slate-500">' . ($alumno->tutor_telefono ?? 'Sin tel') . '</span>' : '-',
                'autorizado' => $alumno->autorizado_retirar ? $alumno->autorizado_retirar . '<br><span class="text-xs font-bold text-slate-500">' . ($alumno->autorizado_telefono ?? 'Sin tel') . '</span>' : '-',
            ];
        }

        $total = $matriculas->count();
        $aulaSeleccionada = $aulaId ? \App\Models\Aula::with('grado')->find($aulaId) : null;

        return [
            'anio' => $anio,
            'aula' => $aulaSeleccionada,
            'total' => $total,
            'con_telefono' => $conTelefono,
            'con_email' => $conEmail,
            'porcentaje_adopcion' => $total > 0 ? round(($conTelefono / $total) * 100, 1) : 0,
            'filas' => $filas,
        ];
    }
}
