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
            // Lanzamos AuthorizationException para que Laravel la resuelva como 403 (y no la atrape el catch genérico)
            throw new \Illuminate\Auth\Access\AuthorizationException('No tiene permisos para acceder a los reportes.');
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
        try {
            $this->autorizarReportes();
            $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
            $resumen = [
                'ingreso_notas' => $this->reporteService->resumenIngresoNotas($anio?->id),
                'estudiantes' => $this->reporteService->estudiantes($anio?->id),
                'padres' => $this->reporteService->padres(),
            ];
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.index', array_merge(compact('anio', 'resumen'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si el hub de reportes falla al cargar los resúmenes estadísticos
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el centro de reportes y estadísticas.');
        }
    }

    // =============================================================
    // CONTROL DE INGRESO DE NOTAS
    // =============================================================
    public function controlNotas(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->controlNotas($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.control-notas', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo generar el reporte de control de notas.');
        }
    }

    public function notasGlobales(Request $request)
    {
        try {
            $this->autorizarReportes();
            $datos = $this->reporteService->notasGlobalesPorAlumno($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            // Evitar que el catálogo 'asignaturas' (select de filtro) pise las columnas filtradas del reporte.
            $columnas = $datos['asignaturas'];
            $filas = $datos['filas'];

            return view('academico.reportes.notas-globales', array_merge(
                compact('columnas', 'filas'),
                $catalogos
            ));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al procesar las calificaciones globales.');
        }
    }

    public function notasPendientes(Request $request)
    {
        try {
            $this->autorizarReportes();
            $pendientes = $this->reporteService->notasPendientes($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.notas-pendientes', array_merge(compact('pendientes'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo cargar el reporte de notas pendientes.');
        }
    }

    // =============================================================
    // ASISTENCIA (segmentada)
    // =============================================================
    public function asistenciaGlobal(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->asistenciaGlobal($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-global', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al cargar la asistencia global.');
        }
    }

    public function estadisticasAsistencia(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->estadisticasAsistencia($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.estadisticas-asistencia', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudieron calcular las estadísticas de asistencia.');
        }
    }

    public function asistenciaSeccionDia(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->asistenciaGlobal($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-seccion-dia', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un problema al generar el reporte de asistencia por sección.');
        }
    }

    public function asistenciaSeccionRango(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->estadisticasAsistencia($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-seccion-rango', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo cargar el reporte de asistencia en rango.');
        }
    }

    public function estadisticasPorEstudiante(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->estadisticasPorEstudiante($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-estudiante', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al procesar las estadísticas individuales de asistencia.');
        }
    }

    // =============================================================
    // RENDIMIENTO ACADÉMICO
    // =============================================================
    public function notasPorAsignatura(Request $request)
    {
        try {
            $this->autorizarReportes();
            $filas = $this->reporteService->notasPorAsignatura($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.notas-por-asignatura', array_merge(compact('filas'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo cargar el reporte de calificaciones por asignatura.');
        }
    }

    public function rendimientoCorte(Request $request)
    {
        try {
            $this->autorizarReportes();
            $datos = $this->reporteService->rendimientoCorteMined($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.rendimiento-corte', array_merge($datos, $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un problema al calcular el rendimiento por corte.');
        }
    }

    public function historialPorEstudiante(Request $request)
    {
        try {
            $this->autorizarReportes();

            $q = trim((string) $request->query('q', ''));
            $gradoId = $request->query('grado_id') ? (int) $request->query('grado_id') : null;
            $anioId = $request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null;
            $alumnoId = $request->query('alumno_id') ? (int) $request->query('alumno_id') : null;

            // Buscador de estudiantes: texto (nombre o CUP) + filtros de grado y año escolar
            $alumnos = Alumno::query()
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($q2) use ($q) {
                        $q2->where('nombre_completo', 'like', "%{$q}%")
                            ->orWhere('codigo_unico_persona', 'like', "%{$q}%");
                    });
                })
                ->when($gradoId || $anioId, function ($query) use ($gradoId, $anioId) {
                    $query->whereHas('matriculas', function ($q2) use ($gradoId, $anioId) {
                        $q2->when($anioId, fn ($q3) => $q3->where('anio_escolar_id', $anioId))
                            ->when($gradoId, fn ($q3) => $q3->whereHas('aula', fn ($q4) => $q4->where('grado_id', $gradoId)));
                    });
                })
                ->orderBy('nombre_completo')
                ->limit(50)
                ->get();

            $resultado = $alumnoId
                ? $this->reporteService->certificadoNotas($alumnoId, $anioId, $gradoId)
                : [
                    'alumno' => null,
                    'anio' => $this->reporteService->resolverAnio($anioId),
                    'matricula' => null,
                    'gradoSeleccionado' => null,
                    'grados' => [],
                    'asignaturas' => [],
                    'limiteGrados' => ReporteService::CERTIFICADO_LIMITE_GRADOS,
                ];

            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.historial-estudiante', array_merge(
                $catalogos,
                $resultado,
                [
                    // La cadena de grados del certificado pisa 'grados'; el filtro usa su propia colección
                    'gradosFiltro' => $catalogos['grados'],
                    'alumnos' => $alumnos,
                    'q' => $q,
                    'gradoId' => $gradoId,
                    'anioId' => $anioId,
                    'alumnoId' => $alumnoId,
                ]
            ));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al generar el certificado de notas del estudiante.');
        }
    }

    // =============================================================
    // OTROS REPORTES
    // =============================================================
    public function mined(Request $request)
    {
        try {
            $this->autorizarReportes();
            $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
            $datos = $this->reporteService->mined($anio?->id);
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.mined', array_merge(compact('anio', 'datos'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo cargar el reporte institucional del MINED.');
        }
    }

    public function estudiantes(Request $request)
    {
        try {
            $this->autorizarReportes();
            $anio = $this->reporteService->resolverAnio($request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null);
            $datos = $this->reporteService->estudiantes($anio?->id);
            $incompletos = Alumno::where(function ($q) {
                $q->whereNull('direccion_domiciliar')->orWhereNull('madre_nombre_completo')
                  ->orWhereNull('madre_telefono')->orWhereNull('tutor_nombre_completo')->orWhereNull('fecha_nacimiento');
            })->orderBy('nombre_completo')->get();
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.estudiantes', array_merge(compact('anio', 'datos', 'incompletos'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al cargar el reporte de estudiantes.');
        }
    }

    public function padres(Request $request)
    {
        try {
            $this->autorizarReportes();
            $datos = $this->reporteService->padres();
            $alumnos = Alumno::orderBy('nombre_completo')->get();
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.padres', array_merge(compact('datos', 'alumnos'), $catalogos));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo cargar el reporte de padres de familia.');
        }
    }
}