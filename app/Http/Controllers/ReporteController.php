<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Docente;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReporteController extends Controller
{
    use AuthorizesRequests;

    protected ReporteService $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    protected function autorizarReportes(): void
    {
        if (!auth()->user()->hasAnyPermission(['reportes.ver', 'reportes.gestionar', 'reportes.supervisar'])) {
            abort(403, 'No tiene permisos para acceder a los reportes.');
        }
    }

    /**
     * Extrae los filtros comunes de la request.
     */
    protected function filtros(Request $request): array
    {
        return [
            'anio_escolar_id' => $request->query('anio_escolar_id'),
            'modalidad_id' => $request->query('modalidad_id'),
            'grado_id' => $request->query('grado_id'),
            'asignatura_id' => $request->query('asignatura_id'),
            'docente_id' => $request->query('docente_id'),
            'corte_evaluativo_id' => $request->query('corte_evaluativo_id'),
            'tipo' => $request->query('tipo'),
            'fecha' => $request->query('fecha'),
            'fecha_inicio' => $request->query('fecha_inicio'),
            'fecha_fin' => $request->query('fecha_fin'),
            'aula_id' => $request->query('aula_id'),
            'alumno_id' => $request->query('alumno_id'),
        ];
    }

    protected function pasarCatalogos(Request $request): array
    {
        $cat = $this->reporteService->catalogos($request->query('anio_escolar_id'));
        return [
            'anios' => $cat['anios'],
            'modalidades' => $cat['modalidades'],
            'grados' => $cat['grados'],
            'asignaturas' => $cat['asignaturas'],
            'cortes' => $cat['cortes'],
            'docentes' => Docente::with('usuario')->orderBy('id')->get(),
            'aulas' => Aula::orderBy('grado_id')->get(),
            'alumnos' => Alumno::orderBy('nombre_completo')->get(),
        ];
    }

    // =============================================================
    // HUB
    // =============================================================
    public function index(Request $request)
    {
        $this->autorizarReportes();
        $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
        $resumen = [
            'ingreso_notas' => $this->reporteService->resumenIngresoNotas($anio?->id),
            'estudiantes' => $this->reporteService->estudiantes($anio?->id),
            'padres' => $this->reporteService->padres(),
        ];
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.index', array_merge(compact('anio', 'resumen'), $catalogos));
    }

    // =============================================================
    // CONTROL DE INGRESO DE NOTAS
    // =============================================================
    public function controlNotas(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->controlNotas($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.control-notas', array_merge(compact('filas'), $catalogos));
    }

    public function notasGlobales(Request $request)
    {
        $this->autorizarReportes();
        $notas = $this->reporteService->notasGlobales($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.notas-globales', array_merge(compact('notas'), $catalogos));
    }

    public function notasPendientes(Request $request)
    {
        $this->autorizarReportes();
        $pendientes = $this->reporteService->notasPendientes($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.notas-pendientes', array_merge(compact('pendientes'), $catalogos));
    }

    // =============================================================
    // ASISTENCIA (segmentada)
    // =============================================================
    public function asistenciaGlobal(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->asistenciaGlobal($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.asistencia-global', array_merge(compact('filas'), $catalogos));
    }

    public function estadisticasAsistencia(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->estadisticasAsistencia($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.estadisticas-asistencia', array_merge(compact('filas'), $catalogos));
    }

    public function asistenciaSeccionDia(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->asistenciaGlobal($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.asistencia-seccion-dia', array_merge(compact('filas'), $catalogos));
    }

    public function asistenciaSeccionRango(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->estadisticasAsistencia($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.asistencia-seccion-rango', array_merge(compact('filas'), $catalogos));
    }

    public function estadisticasPorEstudiante(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->estadisticasPorEstudiante($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.asistencia-estudiante', array_merge(compact('filas'), $catalogos));
    }

    // =============================================================
    // RENDIMIENTO ACADÉMICO
    // =============================================================
    public function notasPorAsignatura(Request $request)
    {
        $this->autorizarReportes();
        $filas = $this->reporteService->notasPorAsignatura($this->filtros($request));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.notas-por-asignatura', array_merge(compact('filas'), $catalogos));
    }

    public function historialPorEstudiante(Request $request)
    {
        $this->autorizarReportes();
        $resultado = $this->reporteService->historialPorEstudiante($request->query('alumno_id'));
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.historial-estudiante', array_merge($resultado, $catalogos));
    }

    // =============================================================
    // OTROS REPORTES
    // =============================================================
    public function mined(Request $request)
    {
        $this->autorizarReportes();
        $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
        $datos = $this->reporteService->mined($anio?->id);
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.mined', array_merge(compact('anio', 'datos'), $catalogos));
    }

    public function estudiantes(Request $request)
    {
        $this->autorizarReportes();
        $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
        $datos = $this->reporteService->estudiantes($anio?->id);
        $incompletos = Alumno::where(function ($q) {
            $q->whereNull('direccion_domiciliar')->orWhereNull('madre_nombre_completo')
              ->orWhereNull('madre_telefono')->orWhereNull('tutor_nombre_completo')->orWhereNull('fecha_nacimiento');
        })->orderBy('nombre_completo')->get();
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.estudiantes', array_merge(compact('anio', 'datos', 'incompletos'), $catalogos));
    }

    public function padres(Request $request)
    {
        $this->autorizarReportes();
        $datos = $this->reporteService->padres();
        $alumnos = Alumno::orderBy('nombre_completo')->get();
        $catalogos = $this->pasarCatalogos($request);

        return view('academico.reportes.padres', array_merge(compact('datos', 'alumnos'), $catalogos));
    }
}