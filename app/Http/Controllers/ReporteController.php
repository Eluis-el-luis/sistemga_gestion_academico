<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
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

    /**
     * Autoriza el acceso al centro de reportes (cualquier permiso de reportes).
     */
    protected function autorizarReportes(): void
    {
        $usuario = auth()->user();

        if (
            !$usuario->hasAnyPermission(['reportes.ver', 'reportes.gestionar', 'reportes.supervisar'])
        ) {
            abort(403, 'No tiene permisos para acceder a los reportes.');
        }
    }

    /**
     * Hub central de reportes con tarjetas de resumen.
     */
    public function index(Request $request)
    {
        $this->autorizarReportes();

        $anioId = $request->query('anio_escolar_id');
        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();

        $resumen = [
            'ingreso_notas' => $this->reporteService->resumenIngresoNotas($anio?->id),
            'estudiantes' => $this->reporteService->estudiantes($anio?->id),
            'padres' => $this->reporteService->padres(),
        ];

        return view('academico.reportes.index', compact('anio', 'anios', 'resumen'));
    }

    /**
     * Reporte: Control de Ingreso de Notas.
     */
    public function ingresoNotas(Request $request)
    {
        $this->autorizarReportes();

        $anioId = $request->query('anio_escolar_id');
        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $filas = $this->reporteService->ingresoNotas($anio?->id);

        return view('academico.reportes.ingreso-notas', compact('anio', 'anios', 'filas'));
    }

    /**
     * Reporte: Asistencia por turno, grado y sección.
     */
    public function asistencia(Request $request)
    {
        $this->autorizarReportes();

        $fecha = $request->query('fecha');
        $anioId = $request->query('anio_escolar_id');
        $modalidadId = $request->query('modalidad_id');

        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $modalidades = Modalidad::all();

        $filas = $this->reporteService->asistencia($fecha, $anio?->id, $modalidadId ? (int) $modalidadId : null);

        return view('academico.reportes.asistencia', compact('anio', 'anios', 'modalidades', 'filas', 'fecha'));
    }

    /**
     * Reporte: Rendimiento Académico por sección.
     */
    public function rendimiento(Request $request)
    {
        $this->autorizarReportes();

        $anioId = $request->query('anio_escolar_id');
        $modalidadId = $request->query('modalidad_id');

        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $modalidades = Modalidad::all();

        $filas = $this->reporteService->rendimiento($anio?->id, $modalidadId ? (int) $modalidadId : null);

        return view('academico.reportes.rendimiento', compact('anio', 'anios', 'modalidades', 'filas'));
    }

    /**
     * Reporte: Formato Oficial MINED.
     */
    public function mined(Request $request)
    {
        $this->autorizarReportes();

        $anioId = $request->query('anio_escolar_id');
        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();

        $datos = $this->reporteService->mined($anio?->id);

        return view('academico.reportes.mined', compact('anio', 'anios', 'datos'));
    }

    /**
     * Reporte: Población Estudiantil (matrícula, retiros, expedientes incompletos).
     */
    public function estudiantes(Request $request)
    {
        $this->autorizarReportes();

        $anioId = $request->query('anio_escolar_id');
        $anio = $this->reporteService->resolverAnio($anioId ? (int) $anioId : null);
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();

        $datos = $this->reporteService->estudiantes($anio?->id);
        $incompletos = \App\Models\Alumno::where(function ($q) {
            $q->whereNull('direccion_domiciliar')
              ->orWhereNull('madre_nombre_completo')
              ->orWhereNull('madre_telefono')
              ->orWhereNull('tutor_nombre_completo')
              ->orWhereNull('fecha_nacimiento');
        })->orderBy('nombre_completo')->get();

        return view('academico.reportes.estudiantes', compact('anio', 'anios', 'datos', 'incompletos'));
    }

    /**
     * Reporte: Responsables y Padres (contacto / adopción digital).
     */
    public function padres(Request $request)
    {
        $this->autorizarReportes();

        $datos = $this->reporteService->padres();
        $alumnos = \App\Models\Alumno::orderBy('nombre_completo')->get();

        return view('academico.reportes.padres', compact('datos', 'alumnos'));
    }
}