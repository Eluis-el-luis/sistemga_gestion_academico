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
        return [
            'anios' => AnioEscolar::orderBy('id', 'desc')->get(),
            'modalidades' => Modalidad::all(),
            'grados' => Grado::orderBy('id')->get(),
            'asignaturas' => Asignatura::orderBy('nombre')->get(),
            'cortes' => CorteEvaluativo::orderBy('numero')->get(),
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
            ];
        }

        return $filas;
    }

    /**
     * Notas globales: listado plano de notas con filtros. Retorna colección de Nota.
     */
    public function notasGlobales(array $filtros)
    {
        $anio = $this->resolverAnio($filtros['anio_escolar_id'] ?? null);

        $query = Nota::with(['matricula.alumno', 'matricula.aula.grado', 'aulaAsignaturaDocente.asignatura', 'corteEvaluativo'])
            ->whereHas('matricula', fn ($q) => $q->where('anio_escolar_id', $anio?->id));

        if (!empty($filtros['asignatura_id'])) {
            $query->whereHas('aulaAsignaturaDocente', fn ($q) => $q->where('asignatura_id', $filtros['asignatura_id']));
        }
        if (!empty($filtros['docente_id'])) {
            $query->whereHas('aulaAsignaturaDocente', fn ($q) => $q->where('docente_id', $filtros['docente_id']));
        }
        if (!empty($filtros['grado_id'])) {
            $query->whereHas('matricula.aula', fn ($q) => $q->where('grado_id', $filtros['grado_id']));
        }
        if (!empty($filtros['corte_evaluativo_id'])) {
            $query->where('corte_evaluativo_id', $filtros['corte_evaluativo_id']);
        }

        return $query->orderBy('matricula_id')->paginate(50)->withQueryString();
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
     * Historial de notas por estudiante (todas las asignaturas y cortes).
     */
    public function historialPorEstudiante(?int $alumnoId): array
    {
        if (!$alumnoId) {
            return ['alumno' => null, 'historial' => collect()];
        }

        $alumno = Alumno::find($alumnoId);
        if (!$alumno) {
            return ['alumno' => null, 'historial' => collect()];
        }

        $historial = Nota::with(['aulaAsignaturaDocente.asignatura', 'corteEvaluativo'])
            ->whereHas('matricula', fn ($q) => $q->where('alumno_id', $alumnoId))
            ->orderBy('corte_evaluativo_id')
            ->get()
            ->groupBy(function ($n) {
                return $n->aulaAsignaturaDocente->asignatura->nombre;
            });

        return ['alumno' => $alumno, 'historial' => $historial];
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