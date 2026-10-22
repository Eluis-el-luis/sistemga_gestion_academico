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

    /**
     * Extrae los catálogos necesarios. 
     * El parámetro $cargarMasivos evita colapsar la memoria en vistas que no necesitan la lista completa de alumnos/docentes.
     */
    protected function pasarCatalogos(Request $request, bool $cargarMasivos = true): array
    {
        $cat = $this->reporteService->catalogos($request->query('anio_escolar_id'));
        
        $datos = [
            'anios' => $cat['anios'],
            'modalidades' => $cat['modalidades'],
            'grados' => $cat['grados'],
            'asignaturas' => $cat['asignaturas'],
            'cortes' => $cat['cortes'],
        ];

        // Solo consultamos estas tablas gigantes si la vista realmente lo requiere
        if ($cargarMasivos) {
            $datos['docentes'] = Docente::with('usuario')->orderBy('id')->get();
            $datos['aulas'] = Aula::orderBy('grado_id')->get();
            $datos['alumnos'] = Alumno::orderBy('nombre_completo')->get();
        } else {
            $datos['docentes'] = collect();
            $datos['aulas'] = collect();
            $datos['alumnos'] = collect();
        }

        return $datos;
    }

    public function asignaturasAprobadas(Request $request)
    {
        $anioId = $request->anio_escolar_id;
        $modalidadId = $request->modalidad_id;
        $corteId = $request->corte_evaluativo_id;

        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $modalidades = \App\Models\Modalidad::all();
        $cortes = \App\Models\CorteEvaluativo::orderBy('numero')->get();

        $corte = $corteId ? \App\Models\CorteEvaluativo::find($corteId) : $cortes->first();
        $corteId = $corte ? $corte->id : null;

        $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
        $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? $corte->numero.' Corte') : 'SELECCIONE CORTE';
        $anioActual = $anioId ? \App\Models\AnioEscolar::find($anioId)->nombre : date('Y');

        $filas = [];
        $listaAsignaturas = collect();

        if ($anioId && $modalidadId && $corteId) {
            // 1. Obtener las asignaturas de la modalidad, EXCLUYENDO TIC y BIBLIA
            $aulasModalidad = \App\Models\Aula::where('modalidad_id', $modalidadId)
                ->where('anio_escolar_id', $anioId)
                ->pluck('id');

            if ($aulasModalidad->isNotEmpty()) {
                $asigIds = \App\Models\AulaAsignaturaDocente::whereIn('aula_id', $aulasModalidad)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('asignatura_id');

                $listaAsignaturas = \App\Models\Asignatura::whereIn('id', $asigIds)
                    ->get()
                    ->reject(function ($asig) {
                        $nombreUpper = mb_strtoupper(trim($asig->nombre));
                        // Filtro institucional para el MINED
                        return str_contains($nombreUpper, 'TIC') || 
                               str_contains($nombreUpper, 'BIBLIA') || 
                               str_contains($nombreUpper, 'INFORMÁTICA');
                    });
            }

            // 2. Obtener los grados de la modalidad
            $grados = \App\Models\Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

            foreach ($grados as $grado) {
                $aulasGrado = \App\Models\Aula::where('grado_id', $grado->id)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('id');

                if ($aulasGrado->isEmpty()) continue;

                $matriculasIniciales = \App\Models\Matricula::with('alumno')
                    ->whereIn('aula_id', $aulasGrado)
                    ->withTrashed()
                    ->get();

                $mi_as = $matriculasIniciales->count();
                $mi_f = $matriculasIniciales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                $ma_as = $matriculasActuales->count();
                $ma_f = $matriculasActuales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $aprobadosEnTodasAs = 0;
                $aprobadosEnTodasF = 0;

                $notas = \App\Models\Nota::whereIn('matricula_id', $matriculasActuales->pluck('id'))
                    ->where('corte_evaluativo_id', $corteId)
                    ->get()
                    ->groupBy('matricula_id');

                $aprobadosPorAsignatura = [];
                foreach ($listaAsignaturas as $asig) {
                    $aprobadosPorAsignatura[$asig->id] = ['as' => 0, 'f' => 0];
                }

                foreach ($matriculasActuales as $matricula) {
                    $notasAlumno = $notas->get($matricula->id, collect());
                    $esFemenino = in_array(strtoupper($matricula->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']);
                    
                    if ($notasAlumno->isEmpty()) continue;

                    $reprobadasTotales = 0;
                    $materiasAprobadasAlumno = [];

                    foreach ($notasAlumno as $nota) {
                        // Omitimos también calificar el promedio de aprobadas en todas si la materia es institucional
                        $asignaturaRelacionada = $listaAsignaturas->firstWhere('id', $nota->asignatura_id);
                        if (!$asignaturaRelacionada) continue; 

                        if ($nota->nota_cuantitativa >= 60) {
                            $materiasAprobadasAlumno[] = $nota->asignatura_id;
                        } else {
                            $reprobadasTotales++;
                        }
                    }

                    if ($reprobadasTotales == 0 && count($materiasAprobadasAlumno) > 0) {
                        $aprobadosEnTodasAs++;
                        if ($esFemenino) $aprobadosEnTodasF++;
                    }

                    foreach ($materiasAprobadasAlumno as $idAsig) {
                        if (isset($aprobadosPorAsignatura[$idAsig])) {
                            $aprobadosPorAsignatura[$idAsig]['as']++;
                            if ($esFemenino) {
                                $aprobadosPorAsignatura[$idAsig]['f']++;
                            }
                        }
                    }
                }

                $filas[] = [
                    'grado' => $grado->nombre,
                    'mi_as' => $mi_as, 'mi_f' => $mi_f,
                    'ma_as' => $ma_as, 'ma_f' => $ma_f,
                    'aprob_todas_as' => $aprobadosEnTodasAs,
                    'aprob_todas_f' => $aprobadosEnTodasF,
                    'por_asignatura' => $aprobadosPorAsignatura,
                ];
            }
        }

        return view('academico.reportes.asignaturas-aprobadas', compact(
            'anios', 'modalidades', 'cortes', 'filas', 'listaAsignaturas', 'infoCentro', 'nombreCorte', 'anioActual'
        ));
    }

    public function escalas(Request $request)
    {
        $anioId = $request->anio_escolar_id;
        $modalidadId = $request->modalidad_id;
        $corteId = $request->corte_evaluativo_id;

        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $modalidades = \App\Models\Modalidad::all();
        $cortes = \App\Models\CorteEvaluativo::orderBy('numero')->get();

        $corte = $corteId ? \App\Models\CorteEvaluativo::find($corteId) : $cortes->first();
        $corteId = $corte ? $corte->id : null;

        $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
        $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? $corte->numero.' Corte') : 'SELECCIONE CORTE';
        $anioActual = $anioId ? \App\Models\AnioEscolar::find($anioId)->nombre : date('Y');

        $filas = [];

        if ($anioId && $modalidadId && $corteId) {
            $grados = \App\Models\Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

            foreach ($grados as $grado) {
                $aulasGrado = \App\Models\Aula::where('grado_id', $grado->id)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('id');

                if ($aulasGrado->isEmpty()) continue;

                $matriculasIniciales = \App\Models\Matricula::with('alumno')
                    ->whereIn('aula_id', $aulasGrado)
                    ->withTrashed()
                    ->get();

                $mi_as = $matriculasIniciales->count();
                $mi_f = $matriculasIniciales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                $ma_as = $matriculasActuales->count();
                $ma_f = $matriculasActuales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $aprob_as = 0; $aprob_f = 0;
                $aa_as = 0; $aa_f = 0; // Avanzado (90-100)
                $as_as = 0; $as_f = 0; // Satisfactorio (75-89)
                $ae_as = 0; $ae_f = 0; // Elemental (60-74)
                $ai_as = 0; $ai_f = 0; // Insuficiente (<60)

                foreach ($matriculasActuales as $matricula) {
                    $esFemenino = in_array(strtoupper($matricula->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']);
                    
                    // Calcular promedio del alumno en el corte
                    $notas = \App\Models\Nota::where('matricula_id', $matricula->id)
                        ->where('corte_evaluativo_id', $corteId)
                        ->pluck('nota_cuantitativa');

                    if ($notas->isEmpty()) continue;
                    $promedio = $notas->avg();

                    if ($promedio >= 60) {
                        $aprob_as++;
                        if ($esFemenino) $aprob_f++;
                    }

                    if ($promedio >= 90) {
                        $aa_as++; if ($esFemenino) $aa_f++;
                    } elseif ($promedio >= 75) {
                        $as_as++; if ($esFemenino) $as_f++;
                    } elseif ($promedio >= 60) {
                        $ae_as++; if ($esFemenino) $ae_f++;
                    } else {
                        $ai_as++; if ($esFemenino) $ai_f++;
                    }
                }

                $filas[] = [
                    'nivel_grado' => $grado->nombre,
                    'mi_as' => $mi_as, 'mi_f' => $mi_f,
                    'ma_as' => $ma_as, 'ma_f' => $ma_f,
                    'aprob_as' => $aprob_as, 'aprob_f' => $aprob_f,
                    'aa_as' => $aa_as, 'aa_f' => $aa_f,
                    'as_as' => $as_as, 'as_f' => $as_f,
                    'ae_as' => $ae_as, 'ae_f' => $ae_f,
                    'ai_as' => $ai_as, 'ai_f' => $ai_f,
                    'valor_as' => 0, 'valor_f' => 0,
                ];
            }
        }

        return view('academico.reportes.escalas', compact(
            'anios', 'modalidades', 'cortes', 'filas', 'infoCentro', 'nombreCorte', 'anioActual'
        ));
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
            
            // Pasamos "false" para que NO cargue miles de alumnos, docentes ni aulas en el Hub principal
            $catalogos = $this->pasarCatalogos($request, false);

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
    // CONTROL DE NOTAS Y AVANCE PROGRAMÁTICO (MINED)
    // =============================================================
    public function controlNotas(Request $request)
    {
        try {
            $this->autorizarReportes();
            
            // Tipo de auditoría en esta misma vista: 'notas' (por defecto) o 'avance'
            $tipoAuditoria = $request->query('tipo_auditoria', 'notas');
            $perspectivaAvance = $request->query('perspectiva', 'docente'); // 'docente' o 'aula'

            $filas = [];
            $estadisticasAvance = ['completados' => 0, 'proceso' => 0, 'atrasados' => 0, 'promedio' => 0];

            if ($tipoAuditoria === 'avance') {
                $filas = $this->reporteService->auditoriaAvanceContenido($this->filtros($request), $perspectivaAvance);
                
                // Calcular estadísticas rápidas para los KPIs de avance
                if (count($filas) > 0) {
                    $suma = array_sum(array_column($filas, 'porcentaje'));
                    $estadisticasAvance['promedio'] = round($suma / count($filas), 1);
                    $estadisticasAvance['completados'] = collect($filas)->where('porcentaje', '>=', 90)->count();
                    $estadisticasAvance['proceso'] = collect($filas)->whereBetween('porcentaje', [50, 89.9])->count();
                    $estadisticasAvance['atrasados'] = collect($filas)->where('porcentaje', '<', 50)->count();
                }
            } else {
                $filas = $this->reporteService->controlNotas($this->filtros($request));
            }

            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.control-notas', array_merge(
                compact('filas', 'tipoAuditoria', 'perspectivaAvance', 'estadisticasAvance'), 
                $catalogos
            ));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'No se pudo generar el reporte de control.');
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
        $anioId = $request->anio_escolar_id;
        $modalidadId = $request->modalidad_id;
        $corteId = $request->corte_evaluativo_id;

        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        $modalidades = \App\Models\Modalidad::all();
        $cortes = \App\Models\CorteEvaluativo::orderBy('numero')->get();

        $corte = $corteId ? \App\Models\CorteEvaluativo::find($corteId) : $cortes->first();
        $corteId = $corte ? $corte->id : null;

        $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
        
        // CORRECCIÓN: Adaptamos a la columna que tengas en BD (descripcion o nombre)
        $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? $corte->numero.' Corte') : 'SELECCIONE CORTE';
        $anioActual = $anioId ? \App\Models\AnioEscolar::find($anioId)->nombre : date('Y');

        $filas = [];
        $totales = [];
        $chartData = ['labels' => [], 'aprobados' => [], 'retencion' => []];

        if ($anioId && $modalidadId && $corteId) {
            $grados = \App\Models\Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

            $t_mi_as = 0; $t_mi_f = 0; $t_ma_as = 0; $t_ma_f = 0;
            $t_aprob_as = 0; $t_aprob_f = 0; 
            $t_aplaz1_2_as = 0; $t_aplaz1_2_f = 0; $t_aplaz3_mas_as = 0; $t_aplaz3_mas_f = 0;
            $t_no_eval_as = 0; $t_no_eval_f = 0;

            foreach ($grados as $grado) {
                $aulas = \App\Models\Aula::where('grado_id', $grado->id)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('id');

                if ($aulas->isEmpty()) continue;

                $matriculasIniciales = \App\Models\Matricula::with('alumno')
                    ->whereIn('aula_id', $aulas)
                    ->withTrashed()
                    ->get();

                $mi_as = $matriculasIniciales->count();
                $mi_f = $matriculasIniciales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                $ma_as = $matriculasActuales->count();
                $ma_f = $matriculasActuales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                $perm_as = $mi_as > 0 ? round(($ma_as / $mi_as) * 100) : 0;
                $perm_f = $mi_f > 0 ? round(($ma_f / $mi_f) * 100) : 0;

                $notas = \App\Models\Nota::whereIn('matricula_id', $matriculasActuales->pluck('id'))
                    ->where('corte_evaluativo_id', $corteId)
                    ->get()
                    ->groupBy('matricula_id');

                $aprob_as = 0; $aprob_f = 0;
                $aplaz1_2_as = 0; $aplaz1_2_f = 0;
                $aplaz3_mas_as = 0; $aplaz3_mas_f = 0;
                $no_evaluado_as = 0; $no_evaluado_f = 0;

                foreach ($matriculasActuales as $matricula) {
                    $notasAlumno = $notas->get($matricula->id, collect());
                    $esFemenino = in_array(strtoupper($matricula->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']);
                    
                    if ($notasAlumno->isEmpty()) {
                        $no_evaluado_as++;
                        if ($esFemenino) $no_evaluado_f++;
                        continue; 
                    }

                    $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();

                    if ($reprobadas == 0) {
                        $aprob_as++;
                        if ($esFemenino) $aprob_f++;
                    } elseif ($reprobadas == 1 || $reprobadas == 2) {
                        $aplaz1_2_as++;
                        if ($esFemenino) $aplaz1_2_f++;
                    } else { 
                        $aplaz3_mas_as++;
                        if ($esFemenino) $aplaz3_mas_f++;
                    }
                }

                $porc_aprob_as = $ma_as > 0 ? round(($aprob_as / $ma_as) * 100) : 0;
                $porc_aprob_f = $ma_f > 0 ? round(($aprob_f / $ma_f) * 100) : 0;

                $eval_as = $ma_as - $no_evaluado_as;
                $eval_f = $ma_f - $no_evaluado_f;

                $t_mi_as += $mi_as; $t_mi_f += $mi_f; $t_ma_as += $ma_as; $t_ma_f += $ma_f;
                $t_aprob_as += $aprob_as; $t_aprob_f += $aprob_f;
                $t_aplaz1_2_as += $aplaz1_2_as; $t_aplaz1_2_f += $aplaz1_2_f;
                $t_aplaz3_mas_as += $aplaz3_mas_as; $t_aplaz3_mas_f += $aplaz3_mas_f;
                $t_no_eval_as += $no_evaluado_as; $t_no_eval_f += $no_evaluado_f;

                $filas[] = [
                    'grado' => $grado->nombre,
                    'mi_as' => $mi_as, 'mi_f' => $mi_f, 'ma_as' => $ma_as, 'ma_f' => $ma_f,
                    'perm_as' => $perm_as, 'perm_f' => $perm_f,
                    'aprob_as' => $aprob_as, 'aprob_f' => $aprob_f,
                    'porc_aprob_as' => $porc_aprob_as, 'porc_aprob_f' => $porc_aprob_f,
                    'aplaz1_2_as' => $aplaz1_2_as, 'aplaz1_2_f' => $aplaz1_2_f,
                    'aplaz3_mas_as' => $aplaz3_mas_as, 'aplaz3_mas_f' => $aplaz3_mas_f,
                    'eval_as' => $eval_as, 'eval_f' => $eval_f,
                    'eval_extra_as' => 0, 'eval_extra_f' => 0,
                    'no_eval_as' => $no_evaluado_as, 'no_eval_f' => $no_evaluado_f,
                    'asist_reforz_as' => 0, 'asist_reforz_f' => 0, 'maest_ref_as' => 0, 'maest_ref_f' => 0,
                ];

                $chartData['labels'][] = $grado->nombre;
                $chartData['aprobados'][] = $porc_aprob_as;
                $chartData['retencion'][] = $perm_as;
            }

            $t_perm_as = $t_mi_as > 0 ? round(($t_ma_as / $t_mi_as) * 100) : 0;
            $t_perm_f = $t_mi_f > 0 ? round(($t_ma_f / $t_mi_f) * 100) : 0;
            $t_porc_aprob_as = $t_ma_as > 0 ? round(($t_aprob_as / $t_ma_as) * 100) : 0;
            $t_porc_aprob_f = $t_ma_f > 0 ? round(($t_aprob_f / $t_ma_f) * 100) : 0;

            $totales = [
                'mi_as' => $t_mi_as, 'mi_f' => $t_mi_f, 'ma_as' => $t_ma_as, 'ma_f' => $t_ma_f,
                'perm_as' => $t_perm_as, 'perm_f' => $t_perm_f,
                'aprob_as' => $t_aprob_as, 'aprob_f' => $t_aprob_f,
                'porc_aprob_as' => $t_porc_aprob_as, 'porc_aprob_f' => $t_porc_aprob_f,
                'aplaz1_2_as' => $t_aplaz1_2_as, 'aplaz1_2_f' => $t_aplaz1_2_f,
                'aplaz3_mas_as' => $t_aplaz3_mas_as, 'aplaz3_mas_f' => $t_aplaz3_mas_f,
                'eval_as' => ($t_ma_as - $t_no_eval_as), 'eval_f' => ($t_ma_f - $t_no_eval_f),
                'eval_extra_as' => 0, 'eval_extra_f' => 0,
                'no_eval_as' => $t_no_eval_as, 'no_eval_f' => $t_no_eval_f,
                'asist_reforz_as' => 0, 'asist_reforz_f' => 0, 'maest_ref_as' => 0, 'maest_ref_f' => 0,
            ];

            $chartData['labels'][] = 'TOTAL';
            $chartData['aprobados'][] = $t_porc_aprob_as;
            $chartData['retencion'][] = $t_perm_as;
        }

        return view('academico.reportes.rendimiento-corte', compact(
            'anios', 'modalidades', 'cortes', 'filas', 'totales', 'chartData', 'infoCentro', 'nombreCorte', 'anioActual'
        ));
    }

    public function historialPorEstudiante(Request $request)
    {
        try {
            $this->autorizarReportes();
            $resultado = $this->reporteService->historialPorEstudiante($request->query('alumno_id'));
            $catalogos = $this->pasarCatalogos($request);

            // Enriquecer con el resumen por asignatura del certificado de notas
            $alumno = $resultado['alumno'] ?? null;
            $resumenAsignaturas = [];
            $promedioGeneral = null;
            $matricula = null;

            if ($alumno) {
                $matricula = \App\Models\Matricula::with(['aula.grado', 'aula.modalidad', 'anioEscolar'])
                    ->where('alumno_id', $alumno->id)
                    ->where('estado', 'activo')
                    ->latest('id')
                    ->first();

                if ($matricula) {
                    $notaService = app(\App\Services\NotaService::class);
                    $asignaciones = \App\Models\AulaAsignaturaDocente::with('asignatura')
                        ->where('aula_id', $matricula->aula_id)
                        ->where('anio_escolar_id', $matricula->anio_escolar_id)
                        ->get();

                    $finales = [];
                    foreach ($asignaciones as $asignacion) {
                        $resumen = $notaService->calcularResumenAsignatura($matricula, $asignacion);
                        $resumenAsignaturas[] = [
                            'asignatura' => $asignacion->asignatura->nombre,
                            'area' => $asignacion->asignatura->area ?? 'Otras Áreas',
                            'resumen' => $resumen,
                        ];
                        if ($resumen['nota_final'] !== null) {
                            $finales[] = $resumen['nota_final'];
                        }
                    }

                    if (count($finales) > 0) {
                        $promedioGeneral = round(array_sum($finales) / count($finales), 2);
                    }
                }
            }

            return view('academico.reportes.historial-estudiante', array_merge(
                ['alumno' => $resultado['alumno'] ?? null, 'resumenAsignaturas' => $resumenAsignaturas, 'promedioGeneral' => $promedioGeneral, 'matricula' => $matricula],
                $catalogos
            ));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.reportes.index')->with('error', 'Ocurrió un error al generar el historial académico del estudiante.');
        }
    }

    // =============================================================
    // OTROS REPORTES
    // =============================================================
    public function mined(Request $request)
    {
        $anioId = $request->anio_escolar_id;
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        
        // Si no hay año seleccionado, busca el activo, si no, el último
        $anio = $anioId ? \App\Models\AnioEscolar::find($anioId) : (\App\Models\AnioEscolar::where('activo', true)->first() ?? $anios->first());
        $anioId = $anio ? $anio->id : null;
        $anioActual = $anio ? $anio->nombre : date('Y');

        $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
        $modalidades = \App\Models\Modalidad::all();
        
        $filas = [];
        $totales = [
            'mi_as' => 0, 'mi_f' => 0,
            'ma_as' => 0, 'ma_f' => 0,
            'ret_as' => 0, 'ret_f' => 0,
            'suma_promedios' => 0, 'conteo_promedios' => 0,
        ];

        if ($anioId) {
            foreach ($modalidades as $mod) {
                $aulas = \App\Models\Aula::where('modalidad_id', $mod->id)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('id');
                    
                if ($aulas->isEmpty()) continue;

                // Matrícula Inicial (Todos)
                $matriculasIniciales = \App\Models\Matricula::with('alumno')
                    ->whereIn('aula_id', $aulas)
                    ->withTrashed()
                    ->get();
                    
                $mi_as = $matriculasIniciales->count();
                $mi_f = $matriculasIniciales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                // Matrícula Actual (Activos)
                $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                $ma_as = $matriculasActuales->count();
                $ma_f = $matriculasActuales->filter(fn($m) => in_array(strtoupper($m->alumno->sexo ?? ''), ['F', 'FEMENINO', 'MUJER']))->count();

                // Retirados
                $ret_as = $mi_as - $ma_as;
                $ret_f = $mi_f - $ma_f;

                // Retención
                $retencion = $mi_as > 0 ? round(($ma_as / $mi_as) * 100, 1) : 0;

                // Promedio Global de esta modalidad
                $notas = \App\Models\Nota::whereIn('matricula_id', $matriculasActuales->pluck('id'))->get();
                $promedio = $notas->count() > 0 ? round($notas->avg('nota_cuantitativa'), 2) : 0;

                // Sumatorias para la fila final
                $totales['mi_as'] += $mi_as; $totales['mi_f'] += $mi_f;
                $totales['ma_as'] += $ma_as; $totales['ma_f'] += $ma_f;
                $totales['ret_as'] += $ret_as; $totales['ret_f'] += $ret_f;
                if ($promedio > 0) {
                    $totales['suma_promedios'] += $promedio;
                    $totales['conteo_promedios']++;
                }

                $filas[] = [
                    'modalidad' => $mod->nombre,
                    'mi_as' => $mi_as, 'mi_f' => $mi_f,
                    'ma_as' => $ma_as, 'ma_f' => $ma_f,
                    'ret_as' => $ret_as, 'ret_f' => $ret_f,
                    'promedio' => number_format($promedio, 2),
                    'retencion' => $retencion
                ];
            }
        }

        // Cálculos finales del total
        $totales['promedio'] = $totales['conteo_promedios'] > 0 ? number_format($totales['suma_promedios'] / $totales['conteo_promedios'], 2) : '0.00';
        $totales['retencion'] = $totales['mi_as'] > 0 ? round(($totales['ma_as'] / $totales['mi_as']) * 100, 1) : 0;

        return view('academico.reportes.mined', compact('anios', 'anioId', 'anioActual', 'filas', 'totales', 'infoCentro'));
    }

    public function avanceProgramatico(Request $request)
    {
        $anioId = $request->anio_escolar_id;
        $anios = \App\Models\AnioEscolar::orderBy('id', 'desc')->get();
        
        $anio = $anioId ? \App\Models\AnioEscolar::find($anioId) : (\App\Models\AnioEscolar::where('activo', true)->first() ?? $anios->first());
        $anioId = $anio ? $anio->id : null;
        $anioActual = $anio ? $anio->nombre : date('Y');

        $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
        $gruposModalidades = [];

        if ($anioId) {
            $asignaciones = \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'aula.modalidad', 'asignatura'])
                ->where('anio_escolar_id', $anioId)
                ->where('activo', true)
                ->get();

            foreach ($asignaciones as $asig) {
                $nombreAsignatura = $asig->asignatura->nombre ?? '';

                // FILTRO INSTITUCIONAL: Excluimos TIC, Biblia y variaciones similares
                $nombreUpper = mb_strtoupper(trim($nombreAsignatura));
                if (str_contains($nombreUpper, 'TIC') || str_contains($nombreUpper, 'BIBLIA') || str_contains($nombreUpper, 'INFORMÁTICA')) {
                    continue; // Salta esta asignatura para que no aparezca en el reporte del MINED
                }

                $modalidadNombre = $asig->aula->modalidad->nombre ?? 'General';
                $avances = \App\Models\AvanceContenido::where('aula_asignatura_docente_id', $asig->id)->get();
                $porcentajeAvance = $avances->count() > 0 ? round($avances->avg('porcentaje_avance'), 1) : 0;
                $ultimoReporte = $avances->sortByDesc('created_at')->first();

                $gruposModalidades[$modalidadNombre][] = [
                    'grado' => $asig->aula->grado->nombre . ' - ' . $asig->aula->nombre,
                    'asignatura' => $nombreAsignatura ?: 'Sin Asignatura',
                    'unidades' => $ultimoReporte->unidades_impartidas ?? 'Unidad I / II',
                    'porcentaje' => $porcentajeAvance,
                ];
            }
        }

        return view('academico.reportes.avance-programatico', compact('anios', 'anioId', 'anioActual', 'gruposModalidades', 'infoCentro'));
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

    // =============================================================
    // REPORTES EXCLUSIVOS DEL DOCENTE POR ASIGNATURA
    // =============================================================
    public function misClases(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user->hasAnyRole(['Docente Guia', 'Docente por Asignatura'])) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Acceso denegado. Esta vista es exclusiva para docentes.');
            }

            $docente = Docente::where('usuario_id', $user->id)->firstOrFail();
            $anio = $this->reporteService->anioActivo();

            if (!$anio) {
                return redirect()->route('dashboard')->with('error', 'No hay un año escolar activo para generar reportes.');
            }

            // 1. Obtenemos las clases y las ORDENAMOS estrictamente por el ID numérico del grado (1ro, 2do, ... 11mo)
            $asignaciones = \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])
                ->where('docente_id', $docente->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('activo', true)
                ->get()
                ->sortBy(function($asignacion) {
                    return $asignacion->aula->grado_id; 
                });

            $cortes = \App\Models\CorteEvaluativo::orderBy('numero')->get();

            // 2. Filtros de búsqueda
            $asignacionId = $request->query('asignacion_id');
            $corteId = $request->query('corte_id');

            $asignacionSeleccionada = null;
            $corteSeleccionado = null;
            $alumnosReporte = collect();
            
            // Inicializamos estadísticas con los porcentajes en 0
            $estadisticas = [
                'aprobados' => 0, 'reprobados' => 0, 'promedio_general' => 0, 
                'porcentaje_aprobados' => 0, 'porcentaje_reprobados' => 0
            ];

            // 3. Procesar tabla si hay selección
            if ($asignacionId) {
                $asignacionSeleccionada = $asignaciones->where('id', $asignacionId)->first();
                $corteSeleccionado = $cortes->where('id', $corteId)->first();

                if ($asignacionSeleccionada) {
                    $matriculas = \App\Models\Matricula::with('alumno')
                        ->where('aula_id', $asignacionSeleccionada->aula_id)
                        ->where('anio_escolar_id', $anio->id)
                        ->where('estado', 'activo')
                        ->get()
                        ->sortBy(function($matricula) {
                            return $matricula->alumno->nombre_completo; // 1. CORREGIDO: Usar nombre_completo para ordenar
                        });

                    $notasQuery = \App\Models\Nota::whereIn('matricula_id', $matriculas->pluck('id'))
                        ->where('aula_asignatura_docente_id', $asignacionSeleccionada->id);
                        
                    if ($corteId) {
                        $notasQuery->where('corte_evaluativo_id', $corteId);
                    }
                    
                    $notas = $notasQuery->get()->groupBy('matricula_id');

                    $sumaNotas = 0;
                    $conNota = 0;

                    foreach ($matriculas as $matricula) {
                        $notasAlumno = $notas->get($matricula->id, collect());
                        $notaFinal = null;
                        
                        if ($notasAlumno->isNotEmpty()) {
                            $notaFinal = round($notasAlumno->avg('nota_cuantitativa'));
                            $sumaNotas += $notaFinal;
                            $conNota++;
                            
                            if ($notaFinal >= 60) {
                                $estadisticas['aprobados']++;
                            } else {
                                $estadisticas['reprobados']++;
                            }
                        }

                        $alumnosReporte->push([
                            'matricula_id' => $matricula->id,
                            'cup' => $matricula->alumno->codigo_unico_persona,
                            'nombre_completo' => $matricula->alumno->nombre_completo, // 2. CORREGIDO: Usar la columna correcta
                            'nota' => $notaFinal
                        ]);
                    }
                    
                    if ($conNota > 0) {
                        $estadisticas['promedio_general'] = round($sumaNotas / $conNota, 1);
                        $estadisticas['porcentaje_aprobados'] = round(($estadisticas['aprobados'] / $conNota) * 100, 1);
                        $estadisticas['porcentaje_reprobados'] = round(($estadisticas['reprobados'] / $conNota) * 100, 1);
                    }
                }
            }

            return view('academico.reportes.mis-clases', compact(
                'anio', 'asignaciones', 'cortes', 'asignacionId', 'corteId', 
                'asignacionSeleccionada', 'corteSeleccionado', 'alumnosReporte', 'estadisticas'
            ));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al procesar el reporte de tus clases.');
        }
    }

    // =============================================================
    // REPORTES EXCLUSIVOS DEL DOCENTE GUÍA (TUTOR)
    // =============================================================
    public function miAula(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user->hasAnyRole(['Docente Guia'])) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Acceso denegado. Esta vista es exclusiva para Tutores.');
            }

            $docente = Docente::where('usuario_id', $user->id)->firstOrFail();
            $anio = $this->reporteService->anioActivo();

            if (!$anio) {
                return redirect()->route('dashboard')->with('error', 'No hay un año escolar activo para generar reportes.');
            }

            // 1. Obtener el aula asignada
            $aulaGuia = \App\Models\Aula::with('grado')
                ->where('docente_guia_id', $docente->id)
                ->where('anio_escolar_id', $anio->id)
                ->first();

            if (!$aulaGuia) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Docente Guía en el ciclo activo.');
            }

            // 2. Filtros
            $cortes = \App\Models\CorteEvaluativo::orderBy('numero')->get();
            $corteId = $request->query('corte_id');
            $corteSeleccionado = $corteId ? $cortes->where('id', $corteId)->first() : null;

            $tipoReporte = $request->query('tipo_reporte', 'general'); // general, riesgo, materia
            $filtroRiesgo = $request->query('filtro_riesgo', 'todos'); // 1_2, 3_mas, todos
            $materiaId = $request->query('materia_id');

            // 3. Asignaturas del Aula
            $asignaciones = \App\Models\AulaAsignaturaDocente::with('asignatura')
                ->where('aula_id', $aulaGuia->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('activo', true)
                ->get()
                ->sortBy(function($asig) {
                    return $asig->asignatura->nombre;
                });

            // Si es reporte por materia, filtramos las asignaciones
            if ($tipoReporte === 'materia' && $materiaId) {
                $asignaciones = $asignaciones->where('id', $materiaId);
            }

            // 4. Obtener Alumnos y Notas
            $matriculas = \App\Models\Matricula::with('alumno')
                ->where('aula_id', $aulaGuia->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(function($matricula) {
                    return $matricula->alumno->nombre_completo;
                });

            $notasQuery = \App\Models\Nota::whereIn('matricula_id', $matriculas->pluck('id'));
            if ($corteId) {
                $notasQuery->where('corte_evaluativo_id', $corteId);
            }
            $notas = $notasQuery->get()->groupBy('matricula_id');

            $alumnosReporte = collect();

            foreach ($matriculas as $matricula) {
                $notasAlumno = $notas->get($matricula->id, collect());
                
                $notasPorMateria = [];
                $detalleReprobadas = [];
                $sumaNotas = 0;
                $materiasEvaluadas = 0;
                $materiasReprobadas = 0;

                foreach ($asignaciones as $asignacion) {
                    $notasMateria = $notasAlumno->where('aula_asignatura_docente_id', $asignacion->id);
                    $notaFinal = null;

                    if ($notasMateria->isNotEmpty()) {
                        $notaFinal = round($notasMateria->avg('nota_cuantitativa'));
                        $sumaNotas += $notaFinal;
                        $materiasEvaluadas++;
                        
                        if ($notaFinal < 60) {
                            $materiasReprobadas++;
                            $detalleReprobadas[] = [
                                'asignatura' => $asignacion->asignatura->nombre,
                                'nota' => $notaFinal
                            ];
                        }
                    }
                    $notasPorMateria[$asignacion->id] = $notaFinal;
                }

                $promedioGeneral = $materiasEvaluadas > 0 ? round($sumaNotas / $materiasEvaluadas, 1) : null;

                // Filtrado Lógico según el tipo de reporte
                $incluirAlumno = true;

                if ($tipoReporte === 'riesgo') {
                    if ($filtroRiesgo === '1_2' && ($materiasReprobadas < 1 || $materiasReprobadas > 2)) {
                        $incluirAlumno = false;
                    } elseif ($filtroRiesgo === '3_mas' && $materiasReprobadas < 3) {
                        $incluirAlumno = false;
                    } elseif ($filtroRiesgo === 'todos' && $materiasReprobadas === 0) {
                        $incluirAlumno = false;
                    }
                }

                if ($incluirAlumno) {
                    $alumnosReporte->push([
                        'matricula_id' => $matricula->id,
                        'cup' => $matricula->alumno->codigo_unico_persona,
                        'nombre_completo' => $matricula->alumno->nombre_completo,
                        'notas' => $notasPorMateria,
                        'promedio_general' => $promedioGeneral,
                        'reprobadas_count' => $materiasReprobadas,
                        'detalle_reprobadas' => $detalleReprobadas
                    ]);
                }
            }

            // Datos para los filtros
            $todasLasAsignaciones = \App\Models\AulaAsignaturaDocente::with('asignatura')
                ->where('aula_id', $aulaGuia->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('activo', true)
                ->get()
                ->sortBy(function($asig) { return $asig->asignatura->nombre; });


            return view('academico.reportes.mi-aula', compact(
                'anio', 'aulaGuia', 'cortes', 'corteId', 'corteSeleccionado', 
                'asignaciones', 'alumnosReporte', 'tipoReporte', 'filtroRiesgo', 'materiaId', 'todasLasAsignaciones'
            ));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el reporte de tu aula.');
        }
    }

}