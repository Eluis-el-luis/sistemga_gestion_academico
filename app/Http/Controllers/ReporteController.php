<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\AvanceContenido;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\Usuario;
use App\Services\ReporteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReporteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReporteService $reporteService
    ) {}

    protected function autorizarReportes(Request $request): void
    {
        /** @var Usuario|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasAnyPermission([
            'reportes.ver',
            'reportes.gestionar',
            'reportes.supervisar',
            'reportes.mi_aula.ver',
            'reportes.mis_clases.ver',
        ]) && !$user->hasAnyRole([
            'directora',
            'subdirectora',
            'secretaria',
            'coordinador',
            'docente_guia',
            'docente_asignatura',
            'docente_auxiliar',
            'docente_suplente',
            'Director',
            'Subdirector',
            'Secretaria',
            'Coordinador',
            'Docente Guia',
            'Docente por Asignatura',
        ])) {
            throw new AuthorizationException('No tiene permisos para acceder a los reportes.');
        }

        if ($user->hasAnyRole(['coordinador', 'Coordinador']) && !$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            $docente = $user->docente;
            if ($docente && $docente->modalidad_coordina_id) {
                $request->merge(['modalidad_id' => $docente->modalidad_coordina_id]);
            }
        }

        if ($user->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía']) && !$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'coordinador', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Coordinador'])) {
            $docente = $user->docente;
            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            if ($docente && $anioActivo) {
                $aula = Aula::where('docente_guia_id', $docente->id)
                    ->where('anio_escolar_id', $anioActivo->id)
                    ->first();
                if ($aula) {
                    $request->merge(['aula_id' => $aula->id, 'grado_id' => $aula->grado_id]);
                }
            }
        }

        $rutaActual = $request->route()?->getName();
        $reportesMinedDocenteGuia = [
            'reportes.rendimiento-corte',
            'reportes.mined-detallado',
            'reportes.escalas',
            'reportes.asignaturas-aprobadas',
        ];

        if ($user->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía']) && in_array($rutaActual, $reportesMinedDocenteGuia, true)) {
            if ($user->hasAnyPermission(['reportes.mi_aula.ver', 'reportes.mis_clases.ver']) === false && $user->permissions->isNotEmpty()) {
                throw new AuthorizationException('No tiene permisos para acceder a este reporte MINED.');
            }
        }
    }

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
            'buscar' => $request->query('buscar'),
        ];
    }

    protected function pasarCatalogos(Request $request, bool $cargarMasivos = true): array
    {
        $cat = $this->reporteService->catalogos(
            $request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null
        );

        $datos = [
            'anios' => $cat['anios'],
            'modalidades' => $cat['modalidades'],
            'grados' => $cat['grados'],
            'asignaturas' => $cat['asignaturas'],
            'cortes' => $cat['cortes'],
        ];

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

    public function asignaturasAprobadas(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anioId = $request->input('anio_escolar_id');
            $modalidadId = $request->input('modalidad_id');
            $corteId = $request->input('corte_evaluativo_id');

            $anios = AnioEscolar::orderByDesc('id')->get();
            $modalidades = Modalidad::all();
            $cortes = CorteEvaluativo::orderBy('numero')->get();

            $corte = $corteId ? CorteEvaluativo::find($corteId) : $cortes->first();
            $corteId = $corte?->id;

            $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
            $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? ($corte->numero . ' Corte')) : 'SELECCIONE CORTE';
            $anioActual = $anioId ? (AnioEscolar::find($anioId)?->nombre ?? date('Y')) : date('Y');

            $filas = [];
            $listaAsignaturas = collect();

            if ($anioId && $modalidadId && $corteId) {
                $aulasModalidad = Aula::where('modalidad_id', $modalidadId)
                    ->where('anio_escolar_id', $anioId)
                    ->pluck('id');

                if ($aulasModalidad->isNotEmpty()) {
                    $asigIds = AulaAsignaturaDocente::whereIn('aula_id', $aulasModalidad)
                        ->where('anio_escolar_id', $anioId)
                        ->pluck('asignatura_id');

                    $listaAsignaturas = Asignatura::whereIn('id', $asigIds)
                        ->get()
                        ->reject(function ($asig) {
                            $nombreUpper = mb_strtoupper(trim($asig->nombre), 'UTF-8');

                            return str_contains($nombreUpper, 'TIC')
                                || str_contains($nombreUpper, 'BIBLIA')
                                || str_contains($nombreUpper, 'INFORMÁTICA');
                        });
                }

                $grados = Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

                foreach ($grados as $grado) {
                    $aulasGrado = Aula::where('grado_id', $grado->id)
                        ->where('anio_escolar_id', $anioId)
                        ->pluck('id');

                    if ($aulasGrado->isEmpty()) {
                        continue;
                    }

                    $matriculasIniciales = Matricula::with('alumno')
                        ->whereIn('aula_id', $aulasGrado)
                        ->withTrashed()
                        ->get();

                    $mi_as = $matriculasIniciales->count();
                    $mi_f = $matriculasIniciales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                    $ma_as = $matriculasActuales->count();
                    $ma_f = $matriculasActuales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $aprobadosEnTodasAs = 0;
                    $aprobadosEnTodasF = 0;

                    $notas = Nota::with('aulaAsignaturaDocente')
                        ->whereIn('matricula_id', $matriculasActuales->pluck('id'))
                        ->where('corte_evaluativo_id', $corteId)
                        ->get()
                        ->groupBy('matricula_id');

                    $aprobadosPorAsignatura = [];
                    foreach ($listaAsignaturas as $asig) {
                        $aprobadosPorAsignatura[$asig->id] = ['as' => 0, 'f' => 0];
                    }

                    foreach ($matriculasActuales as $matricula) {
                        $notasAlumno = $notas->get($matricula->id, collect());
                        $esFemenino = in_array(strtoupper((string) ($matricula->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true);

                        if ($notasAlumno->isEmpty()) {
                            continue;
                        }

                        $reprobadasTotales = 0;
                        $materiasAprobadasAlumno = [];

                        foreach ($notasAlumno as $nota) {
                            $idAsigRel = $nota->asignatura_id ?? $nota->aulaAsignaturaDocente?->asignatura_id;
                            $asignaturaRelacionada = $listaAsignaturas->firstWhere('id', $idAsigRel);
                            if (!$asignaturaRelacionada) {
                                continue;
                            }

                            if (!is_null($nota->nota_cuantitativa) && (float) $nota->nota_cuantitativa >= 60) {
                                $materiasAprobadasAlumno[] = $asignaturaRelacionada->id;
                            } else {
                                $reprobadasTotales++;
                            }
                        }

                        if ($reprobadasTotales === 0 && $materiasAprobadasAlumno !== []) {
                            $aprobadosEnTodasAs++;
                            if ($esFemenino) {
                                $aprobadosEnTodasF++;
                            }
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
                        'mi_as' => $mi_as,
                        'mi_f' => $mi_f,
                        'ma_as' => $ma_as,
                        'ma_f' => $ma_f,
                        'aprob_todas_as' => $aprobadosEnTodasAs,
                        'aprob_todas_f' => $aprobadosEnTodasF,
                        'por_asignatura' => $aprobadosPorAsignatura,
                    ];
                }
            }

            return view('academico.reportes.asignaturas-aprobadas', compact(
                'anios',
                'modalidades',
                'cortes',
                'filas',
                'listaAsignaturas',
                'infoCentro',
                'nombreCorte',
                'anioActual'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte de asignaturas aprobadas.'));
        }
    }

    public function escalas(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anioId = $request->input('anio_escolar_id');
            $modalidadId = $request->input('modalidad_id');
            $corteId = $request->input('corte_evaluativo_id');

            $anios = AnioEscolar::orderByDesc('id')->get();
            $modalidades = Modalidad::all();
            $cortes = CorteEvaluativo::orderBy('numero')->get();

            $corte = $corteId ? CorteEvaluativo::find($corteId) : $cortes->first();
            $corteId = $corte?->id;

            $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
            $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? ($corte->numero . ' Corte')) : 'SELECCIONE CORTE';
            $anioActual = $anioId ? (AnioEscolar::find($anioId)?->nombre ?? date('Y')) : date('Y');

            $filas = [];

            if ($anioId && $modalidadId && $corteId) {
                $grados = Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

                foreach ($grados as $grado) {
                    $aulasGrado = Aula::where('grado_id', $grado->id)
                        ->where('anio_escolar_id', $anioId)
                        ->pluck('id');

                    if ($aulasGrado->isEmpty()) {
                        continue;
                    }

                    $matriculasIniciales = Matricula::with('alumno')
                        ->whereIn('aula_id', $aulasGrado)
                        ->withTrashed()
                        ->get();

                    $mi_as = $matriculasIniciales->count();
                    $mi_f = $matriculasIniciales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                    $ma_as = $matriculasActuales->count();
                    $ma_f = $matriculasActuales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $aprob_as = 0;
                    $aprob_f = 0;
                    $aa_as = 0;
                    $aa_f = 0;
                    $as_as = 0;
                    $as_f = 0;
                    $ae_as = 0;
                    $ae_f = 0;
                    $ai_as = 0;
                    $ai_f = 0;

                    foreach ($matriculasActuales as $matricula) {
                        $esFemenino = in_array(strtoupper((string) ($matricula->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true);

                        $notas = Nota::where('matricula_id', $matricula->id)
                            ->where('corte_evaluativo_id', $corteId)
                            ->pluck('nota_cuantitativa')
                            ->filter(fn ($n) => !is_null($n));

                        if ($notas->isEmpty()) {
                            continue;
                        }

                        $promedio = (float) $notas->avg();

                        if ($promedio >= 60) {
                            $aprob_as++;
                            if ($esFemenino) {
                                $aprob_f++;
                            }
                        }

                        if ($promedio >= 90) {
                            $aa_as++;
                            if ($esFemenino) {
                                $aa_f++;
                            }
                        } elseif ($promedio >= 75) {
                            $as_as++;
                            if ($esFemenino) {
                                $as_f++;
                            }
                        } elseif ($promedio >= 60) {
                            $ae_as++;
                            if ($esFemenino) {
                                $ae_f++;
                            }
                        } else {
                            $ai_as++;
                            if ($esFemenino) {
                                $ai_f++;
                            }
                        }
                    }

                    $filas[] = [
                        'nivel_grado' => $grado->nombre,
                        'mi_as' => $mi_as,
                        'mi_f' => $mi_f,
                        'ma_as' => $ma_as,
                        'ma_f' => $ma_f,
                        'aprob_as' => $aprob_as,
                        'aprob_f' => $aprob_f,
                        'aa_as' => $aa_as,
                        'aa_f' => $aa_f,
                        'as_as' => $as_as,
                        'as_f' => $as_f,
                        'ae_as' => $ae_as,
                        'ae_f' => $ae_f,
                        'ai_as' => $ai_as,
                        'ai_f' => $ai_f,
                        'valor_as' => 0,
                        'valor_f' => 0,
                    ];
                }
            }

            return view('academico.reportes.escalas', compact(
                'anios',
                'modalidades',
                'cortes',
                'filas',
                'infoCentro',
                'nombreCorte',
                'anioActual'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte de escalas.'));
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $anio = $this->reporteService->resolverAnio(
                $request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null
            );
            $resumen = [
                'ingreso_notas' => $this->reporteService->resumenIngresoNotas($anio?->id),
                'estudiantes' => $this->reporteService->estudiantes($anio?->id),
                'padres' => $this->reporteService->padres(),
            ];
            $catalogos = $this->pasarCatalogos($request, false);

            return view('academico.reportes.index', array_merge(compact('anio', 'resumen'), $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el centro de reportes.'));
        }
    }

    public function controlNotas(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $tipoAuditoria = (string) $request->query('tipo_auditoria', 'notas');
            $perspectivaAvance = (string) $request->query('perspectiva', 'docente');
            $filas = [];
            $estadisticasAvance = ['completados' => 0, 'proceso' => 0, 'atrasados' => 0, 'promedio' => 0];

            if ($tipoAuditoria === 'avance') {
                $filas = $this->reporteService->auditoriaAvanceContenido($this->filtros($request), $perspectivaAvance);

                if ($filas !== []) {
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
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte de control de notas.'));
        }
    }

    public function notasGlobales(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->notasGlobalesPorAlumno($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.notas-globales', array_merge(
                $catalogos,
                [
                    'asignaturas' => $datos['asignaturas'],
                    'filas' => $datos['filas'],
                    'aula' => $datos['aula'] ?? null,
                ]
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar las calificaciones globales.'));
        }
    }

    public function notasPendientes(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $filas = $this->reporteService->notasPendientes($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.notas-pendientes', array_merge(compact('filas'), $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de notas pendientes.'));
        }
    }

    public function asistenciaGlobal(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->asistenciaGlobal($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-global', array_merge($datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar la asistencia global.'));
        }
    }

    public function estadisticasAsistencia(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->estadisticasAsistencia($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.estadisticas-asistencia', array_merge($datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible calcular las estadísticas de asistencia.'));
        }
    }

    public function asistenciaPorSeccion(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->asistenciaPorSeccion($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-por-seccion', array_merge($datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte de asistencia por sección.'));
        }
    }

    public function asistenciaSeccionRango(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->asistenciaSeccionRango($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-seccion-rango', array_merge($datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de asistencia en rango.'));
        }
    }

    public function estadisticasPorEstudiante(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->asistenciaEstudiante($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.asistencia-estudiante', array_merge([
                'fecha_inicio' => $request->query('fecha_inicio') ?? now()->subMonth()->toDateString(),
                'fecha_fin' => $request->query('fecha_fin') ?? now()->toDateString(),
            ], $datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible procesar las estadísticas por estudiante.'));
        }
    }

    public function reporteMinedDetallado(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $filtros = $request->all();

            $esDocenteGuia = false;
            $aulaGuia = null;

            /** @var Usuario $user */
            $user = Auth::user();

            if ($user->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía']) && !$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
                $esDocenteGuia = true;
                $docente = Docente::where('usuario_id', $user->id)->first();
                $anioActivo = AnioEscolar::where('estado', 'activo')->first();

                if ($docente && $anioActivo) {
                    $aulaGuia = Aula::with('grado')
                        ->where('docente_guia_id', $docente->id)
                        ->where('anio_escolar_id', $anioActivo->id)
                        ->first();

                    if ($aulaGuia) {
                        $filtros['grado_id'] = $aulaGuia->grado_id;
                    } else {
                        return redirect()->route('dashboard')
                            ->with('error', 'No tienes un aula asignada como tutor en este año escolar.');
                    }
                }
            }

            $datos = $this->reporteService->rendimientoMinedDetallado($filtros);
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.mined-detallado', array_merge($datos, $catalogos, compact('esDocenteGuia', 'aulaGuia')));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte MINED detallado.'));
        }
    }

    public function notasPorAsignatura(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $filas = $this->reporteService->notasPorAsignatura($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.notas-por-asignatura', array_merge(compact('filas'), $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de notas por asignatura.'));
        }
    }

    public function rendimientoCorte(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anioId = $request->input('anio_escolar_id');
            $modalidadId = $request->input('modalidad_id');
            $corteId = $request->input('corte_evaluativo_id');

            $anios = AnioEscolar::orderByDesc('id')->get();
            $modalidades = Modalidad::all();
            $cortes = CorteEvaluativo::orderBy('numero')->get();

            $corte = $corteId ? CorteEvaluativo::find($corteId) : $cortes->first();
            $corteId = $corte?->id;

            $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
            $nombreCorte = $corte ? ($corte->descripcion ?? $corte->nombre ?? ($corte->numero . ' Corte')) : 'SELECCIONE CORTE';
            $anioActual = $anioId ? (AnioEscolar::find($anioId)?->nombre ?? date('Y')) : date('Y');

            $filas = [];
            $totales = [];
            $chartData = ['labels' => [], 'aprobados' => [], 'retencion' => []];

            if ($anioId && $modalidadId && $corteId) {
                $grados = Grado::where('modalidad_id', $modalidadId)->orderBy('id')->get();

                $t_mi_as = 0;
                $t_mi_f = 0;
                $t_ma_as = 0;
                $t_ma_f = 0;
                $t_aprob_as = 0;
                $t_aprob_f = 0;
                $t_aplaz1_2_as = 0;
                $t_aplaz1_2_f = 0;
                $t_aplaz3_mas_as = 0;
                $t_aplaz3_mas_f = 0;
                $t_no_eval_as = 0;
                $t_no_eval_f = 0;

                foreach ($grados as $grado) {
                    $aulas = Aula::where('grado_id', $grado->id)
                        ->where('anio_escolar_id', $anioId)
                        ->pluck('id');

                    if ($aulas->isEmpty()) {
                        continue;
                    }

                    $matriculasIniciales = Matricula::with('alumno')
                        ->whereIn('aula_id', $aulas)
                        ->withTrashed()
                        ->get();

                    $mi_as = $matriculasIniciales->count();
                    $mi_f = $matriculasIniciales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                    $ma_as = $matriculasActuales->count();
                    $ma_f = $matriculasActuales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $perm_as = $mi_as > 0 ? (int) round(($ma_as / $mi_as) * 100) : 0;
                    $perm_f = $mi_f > 0 ? (int) round(($ma_f / $mi_f) * 100) : 0;

                    $notas = Nota::whereIn('matricula_id', $matriculasActuales->pluck('id'))
                        ->where('corte_evaluativo_id', $corteId)
                        ->get()
                        ->groupBy('matricula_id');

                    $aprob_as = 0;
                    $aprob_f = 0;
                    $aplaz1_2_as = 0;
                    $aplaz1_2_f = 0;
                    $aplaz3_mas_as = 0;
                    $aplaz3_mas_f = 0;
                    $no_evaluado_as = 0;
                    $no_evaluado_f = 0;

                    foreach ($matriculasActuales as $matricula) {
                        $notasAlumno = $notas->get($matricula->id, collect());
                        $esFemenino = in_array(strtoupper((string) ($matricula->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true);

                        if ($notasAlumno->isEmpty()) {
                            $no_evaluado_as++;
                            if ($esFemenino) {
                                $no_evaluado_f++;
                            }
                            continue;
                        }

                        $reprobadas = $notasAlumno->where('nota_cuantitativa', '<', 60)->count();

                        if ($reprobadas === 0) {
                            $aprob_as++;
                            if ($esFemenino) {
                                $aprob_f++;
                            }
                        } elseif ($reprobadas <= 2) {
                            $aplaz1_2_as++;
                            if ($esFemenino) {
                                $aplaz1_2_f++;
                            }
                        } else {
                            $aplaz3_mas_as++;
                            if ($esFemenino) {
                                $aplaz3_mas_f++;
                            }
                        }
                    }

                    $porc_aprob_as = $ma_as > 0 ? (int) round(($aprob_as / $ma_as) * 100) : 0;
                    $porc_aprob_f = $ma_f > 0 ? (int) round(($aprob_f / $ma_f) * 100) : 0;

                    $eval_as = $ma_as - $no_evaluado_as;
                    $eval_f = $ma_f - $no_evaluado_f;

                    $t_mi_as += $mi_as;
                    $t_mi_f += $mi_f;
                    $t_ma_as += $ma_as;
                    $t_ma_f += $ma_f;
                    $t_aprob_as += $aprob_as;
                    $t_aprob_f += $aprob_f;
                    $t_aplaz1_2_as += $aplaz1_2_as;
                    $t_aplaz1_2_f += $aplaz1_2_f;
                    $t_aplaz3_mas_as += $aplaz3_mas_as;
                    $t_aplaz3_mas_f += $aplaz3_mas_f;
                    $t_no_eval_as += $no_evaluado_as;
                    $t_no_eval_f += $no_evaluado_f;

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

                $t_perm_as = $t_mi_as > 0 ? (int) round(($t_ma_as / $t_mi_as) * 100) : 0;
                $t_perm_f = $t_mi_f > 0 ? (int) round(($t_ma_f / $t_mi_f) * 100) : 0;
                $t_porc_aprob_as = $t_ma_as > 0 ? (int) round(($t_aprob_as / $t_ma_as) * 100) : 0;
                $t_porc_aprob_f = $t_ma_f > 0 ? (int) round(($t_aprob_f / $t_ma_f) * 100) : 0;

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
                'anios',
                'modalidades',
                'cortes',
                'filas',
                'totales',
                'chartData',
                'infoCentro',
                'nombreCorte',
                'anioActual'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte de rendimiento por corte.'));
        }
    }

    public function historialPorEstudiante(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $q = trim((string) $request->query('q', ''));
            $alumnoId = $request->query('alumno_id') ? (int) $request->query('alumno_id') : null;
            $matriculaId = $request->query('matricula_id') ? (int) $request->query('matricula_id') : null;

            $alumnos = Alumno::query()
                ->when($q !== '', function ($query) use ($q) {
                    $termino = '%' . mb_strtolower($q, 'UTF-8') . '%';
                    $query->where(function ($q2) use ($termino) {
                        $q2->whereRaw('LOWER(nombre_completo) LIKE ?', [$termino])
                            ->orWhereRaw('LOWER(codigo_unico_persona) LIKE ?', [$termino]);
                    });
                })
                ->orderBy('nombre_completo')
                ->limit(50)
                ->get();

            $alumno = $alumnoId ? Alumno::find($alumnoId) : null;
            $matriculasAlumno = collect();
            $matriculaSel = null;

            if ($alumno) {
                $matriculasAlumno = Matricula::with(['aula.grado.modalidad', 'anioEscolar'])
                    ->where('alumno_id', $alumno->id)
                    ->get()
                    ->sortByDesc('anio_escolar_id')
                    ->values();

                $anioActivo = $this->reporteService->anioActivo();
                $matriculaSel = $matriculaId
                    ? $matriculasAlumno->firstWhere('id', $matriculaId)
                    : ($matriculasAlumno->firstWhere('anio_escolar_id', $anioActivo?->id) ?? $matriculasAlumno->first());
            }

            $resultado = $alumno
                ? ($matriculaSel
                    ? $this->reporteService->certificadoNotas($alumno->id, $matriculaSel->anio_escolar_id, $matriculaSel->aula?->grado_id)
                    : [
                        'alumno' => $alumno,
                        'anio' => $this->reporteService->resolverAnio(null),
                        'matricula' => null,
                        'gradoSeleccionado' => null,
                        'grados' => [],
                        'asignaturas' => [],
                        'limiteGrados' => ReporteService::CERTIFICADO_LIMITE_GRADOS,
                    ])
                : [
                    'alumno' => null,
                    'anio' => $this->reporteService->resolverAnio(null),
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
                    'gradosFiltro' => $catalogos['grados'],
                    'alumnos' => $alumnos,
                    'matriculasAlumno' => $matriculasAlumno,
                    'matriculaSel' => $matriculaSel,
                    'q' => $q,
                    'alumnoId' => $alumnoId,
                ]
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el historial del estudiante.'));
        }
    }

    public function mined(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anioId = $request->input('anio_escolar_id');
            $anios = AnioEscolar::orderByDesc('id')->get();
            $anio = $anioId ? AnioEscolar::find($anioId) : (AnioEscolar::where('estado', 'activo')->first() ?? $anios->first());
            $anioId = $anio?->id;
            $anioActual = $anio?->nombre ?? date('Y');

            $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
            $modalidades = Modalidad::all();
            $filas = [];
            $totales = [
                'mi_as' => 0, 'mi_f' => 0,
                'ma_as' => 0, 'ma_f' => 0,
                'ret_as' => 0, 'ret_f' => 0,
                'suma_promedios' => 0.0, 'conteo_promedios' => 0,
            ];

            if ($anioId) {
                foreach ($modalidades as $mod) {
                    $aulas = Aula::where('modalidad_id', $mod->id)
                        ->where('anio_escolar_id', $anioId)
                        ->pluck('id');

                    if ($aulas->isEmpty()) {
                        continue;
                    }

                    $matriculasIniciales = Matricula::with('alumno')
                        ->whereIn('aula_id', $aulas)
                        ->withTrashed()
                        ->get();

                    $mi_as = $matriculasIniciales->count();
                    $mi_f = $matriculasIniciales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $matriculasActuales = $matriculasIniciales->where('estado', 'activo');
                    $ma_as = $matriculasActuales->count();
                    $ma_f = $matriculasActuales->filter(fn ($m) => in_array(strtoupper((string) ($m->alumno->sexo ?? '')), ['F', 'FEMENINO', 'MUJER'], true))->count();

                    $ret_as = $mi_as - $ma_as;
                    $ret_f = $mi_f - $ma_f;
                    $retencion = $mi_as > 0 ? round(($ma_as / $mi_as) * 100, 1) : 0;

                    $notas = Nota::whereIn('matricula_id', $matriculasActuales->pluck('id'))
                        ->whereNotNull('nota_cuantitativa')
                        ->get();
                    $promedio = $notas->isNotEmpty() ? round((float) $notas->avg('nota_cuantitativa'), 2) : 0;

                    $totales['mi_as'] += $mi_as;
                    $totales['mi_f'] += $mi_f;
                    $totales['ma_as'] += $ma_as;
                    $totales['ma_f'] += $ma_f;
                    $totales['ret_as'] += $ret_as;
                    $totales['ret_f'] += $ret_f;

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
                        'retencion' => $retencion,
                    ];
                }
            }

            $totales['promedio'] = $totales['conteo_promedios'] > 0
                ? number_format($totales['suma_promedios'] / $totales['conteo_promedios'], 2)
                : '0.00';
            $totales['retencion'] = $totales['mi_as'] > 0 ? round(($totales['ma_as'] / $totales['mi_as']) * 100, 1) : 0;

            return view('academico.reportes.mined', compact('anios', 'anioId', 'anioActual', 'filas', 'totales', 'infoCentro'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible generar el reporte MINED.'));
        }
    }

    public function avanceProgramatico(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anioId = $request->input('anio_escolar_id');
            $anios = AnioEscolar::orderByDesc('id')->get();
            $anio = $anioId ? AnioEscolar::find($anioId) : (AnioEscolar::where('estado', 'activo')->first() ?? $anios->first());
            $anioId = $anio?->id;
            $anioActual = $anio?->nombre ?? date('Y');

            $infoCentro = ['cod_establecimiento' => '27974', 'cod_centro' => '27760'];
            $gruposModalidades = [];

            if ($anioId) {
                $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'aula.modalidad', 'asignatura'])
                    ->where('anio_escolar_id', $anioId)
                    ->where('activo', true)
                    ->get();

                foreach ($asignaciones as $asig) {
                    $nombreAsignatura = $asig->asignatura->nombre ?? '';
                    $nombreUpper = mb_strtoupper(trim($nombreAsignatura), 'UTF-8');

                    if (
                        str_contains($nombreUpper, 'TIC')
                        || str_contains($nombreUpper, 'BIBLIA')
                        || str_contains($nombreUpper, 'INFORMÁTICA')
                    ) {
                        continue;
                    }

                    $modalidadNombre = $asig->aula?->modalidad?->nombre ?? 'General';
                    $avances = AvanceContenido::where('aula_asignatura_docente_id', $asig->id)->get();
                    $porcentajeAvance = $avances->isNotEmpty() ? round((float) $avances->avg('porcentaje_avance'), 1) : 0;
                    $ultimoReporte = $avances->sortByDesc('created_at')->first();

                    $gruposModalidades[$modalidadNombre][] = [
                        'grado' => ($asig->aula?->grado?->nombre ?? '') . ' - ' . ($asig->aula?->nombre ?? ''),
                        'asignatura' => $nombreAsignatura !== '' ? $nombreAsignatura : 'Sin Asignatura',
                        'unidades' => $ultimoReporte->unidades_impartidas ?? 'Unidad I / II',
                        'porcentaje' => $porcentajeAvance,
                    ];
                }
            }

            return view('academico.reportes.avance-programatico', compact('anios', 'anioId', 'anioActual', 'gruposModalidades', 'infoCentro'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el avance programático.'));
        }
    }

    public function estudiantes(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);

            $anio = $this->reporteService->resolverAnio(
                $request->query('anio_escolar_id') ? (int) $request->query('anio_escolar_id') : null
            );
            $datos = $this->reporteService->estudiantes($anio?->id);
            $incompletos = Alumno::where(function ($q) {
                $q->whereNull('direccion_domiciliar')
                    ->orWhereNull('madre_nombre_completo')
                    ->orWhereNull('madre_telefono')
                    ->orWhereNull('tutor_nombre_completo')
                    ->orWhereNull('fecha_nacimiento');
            })->orderBy('nombre_completo')->get();

            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.estudiantes', array_merge(compact('anio', 'datos', 'incompletos'), $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de estudiantes.'));
        }
    }

    public function padres(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizarReportes($request);
            $datos = $this->reporteService->padres($this->filtros($request));
            $catalogos = $this->pasarCatalogos($request);

            return view('academico.reportes.padres', array_merge($datos, $catalogos));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.reportes.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de padres de familia.'));
        }
    }

    public function misClases(Request $request): View|RedirectResponse
    {
        try {
            /** @var Usuario $user */
            $user = Auth::user();
            if (!$user->hasAnyRole(['docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente Guia', 'Docente por Asignatura'])) {
                throw new AuthorizationException('Acceso denegado. Esta vista es exclusiva para docentes.');
            }

            $docente = Docente::where('usuario_id', $user->id)->firstOrFail();
            $anio = $this->reporteService->anioActivo();

            if (!$anio) {
                return redirect()->route('dashboard')
                    ->with('error', 'No hay un año escolar activo para generar reportes.');
            }

            $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])
                ->where('docente_id', $docente->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('activo', true)
                ->get()
                ->sortBy(fn ($asignacion) => $asignacion->aula?->grado_id ?? 0);

            $cortes = CorteEvaluativo::orderBy('numero')->get();
            $asignacionId = $request->query('asignacion_id');
            $corteId = $request->query('corte_id');

            $asignacionSeleccionada = null;
            $corteSeleccionado = null;
            $alumnosReporte = collect();

            $estadisticas = [
                'aprobados' => 0,
                'reprobados' => 0,
                'promedio_general' => 0,
                'porcentaje_aprobados' => 0,
                'porcentaje_reprobados' => 0,
            ];

            if ($asignacionId) {
                $asignacionSeleccionada = $asignaciones->where('id', (int) $asignacionId)->first();
                $corteSeleccionado = $cortes->where('id', (int) $corteId)->first();

                if ($asignacionSeleccionada) {
                    $matriculas = Matricula::with('alumno')
                        ->where('aula_id', $asignacionSeleccionada->aula_id)
                        ->where('anio_escolar_id', $anio->id)
                        ->where('estado', 'activo')
                        ->get()
                        ->sortBy(fn ($matricula) => $matricula->alumno->nombre_completo ?? '');

                    $notas = Nota::whereIn('matricula_id', $matriculas->pluck('id'))
                        ->where('aula_asignatura_docente_id', $asignacionSeleccionada->id)
                        ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', (int) $corteId))
                        ->get()
                        ->groupBy('matricula_id');

                    $sumaNotas = 0.0;
                    $conNota = 0;

                    foreach ($matriculas as $matricula) {
                        $notasAlumno = $notas->get($matricula->id, collect())->filter(fn ($n) => !is_null($n->nota_cuantitativa));
                        $notaFinal = null;

                        if ($notasAlumno->isNotEmpty()) {
                            $notaFinal = (int) round((float) $notasAlumno->avg('nota_cuantitativa'));
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
                            'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                            'nombre_completo' => $matricula->alumno->nombre_completo ?? '',
                            'nota' => $notaFinal,
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
                'anio',
                'asignaciones',
                'cortes',
                'asignacionId',
                'corteId',
                'asignacionSeleccionada',
                'corteSeleccionado',
                'alumnosReporte',
                'estadisticas'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de tus clases.'));
        }
    }

    public function miAula(Request $request): View|RedirectResponse
    {
        try {
            /** @var Usuario $user */
            $user = Auth::user();
            if (!$user->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía'])) {
                throw new AuthorizationException('Acceso denegado. Esta vista es exclusiva para Tutores.');
            }

            $docente = Docente::where('usuario_id', $user->id)->firstOrFail();
            $anio = $this->reporteService->anioActivo();

            if (!$anio) {
                return redirect()->route('dashboard')
                    ->with('error', 'No hay un año escolar activo para generar reportes.');
            }

            $aulaGuia = Aula::with('grado')
                ->where('docente_guia_id', $docente->id)
                ->where('anio_escolar_id', $anio->id)
                ->first();

            if (!$aulaGuia) {
                return redirect()->route('dashboard')
                    ->with('error', 'No tienes un aula asignada como Docente Guía en el ciclo activo.');
            }

            $cortes = CorteEvaluativo::orderBy('numero')->get();
            $corteId = $request->query('corte_id');
            $corteSeleccionado = $corteId ? $cortes->where('id', (int) $corteId)->first() : null;

            $tipoReporte = (string) $request->query('tipo_reporte', 'general');
            $filtroRiesgo = (string) $request->query('filtro_riesgo', 'todos');
            $materiaId = $request->query('materia_id');

            $todasLasAsignaciones = AulaAsignaturaDocente::with('asignatura')
                ->where('aula_id', $aulaGuia->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('activo', true)
                ->get()
                ->sortBy(fn ($asig) => $asig->asignatura->nombre ?? '');

            $asignaciones = ($tipoReporte === 'materia' && $materiaId)
                ? $todasLasAsignaciones->where('id', (int) $materiaId)
                : $todasLasAsignaciones;

            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aulaGuia->id)
                ->where('anio_escolar_id', $anio->id)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn ($matricula) => $matricula->alumno->nombre_completo ?? '');

            $notas = Nota::whereIn('matricula_id', $matriculas->pluck('id'))
                ->when($corteId, fn ($q) => $q->where('corte_evaluativo_id', (int) $corteId))
                ->get()
                ->groupBy('matricula_id');

            $alumnosReporte = collect();

            foreach ($matriculas as $matricula) {
                $notasAlumno = $notas->get($matricula->id, collect());
                $notasPorMateria = [];
                $detalleReprobadas = [];
                $sumaNotas = 0.0;
                $materiasEvaluadas = 0;
                $materiasReprobadas = 0;

                foreach ($asignaciones as $asignacion) {
                    $notasMateria = $notasAlumno
                        ->where('aula_asignatura_docente_id', $asignacion->id)
                        ->filter(fn ($n) => !is_null($n->nota_cuantitativa));

                    $notaFinal = null;
                    if ($notasMateria->isNotEmpty()) {
                        $notaFinal = (int) round((float) $notasMateria->avg('nota_cuantitativa'));
                        $sumaNotas += $notaFinal;
                        $materiasEvaluadas++;

                        if ($notaFinal < 60) {
                            $materiasReprobadas++;
                            $detalleReprobadas[] = [
                                'asignatura' => $asignacion->asignatura->nombre ?? '',
                                'nota' => $notaFinal,
                            ];
                        }
                    }
                    $notasPorMateria[$asignacion->id] = $notaFinal;
                }

                $promedioGeneral = $materiasEvaluadas > 0 ? round($sumaNotas / $materiasEvaluadas, 1) : null;
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
                        'cup' => $matricula->alumno->codigo_unico_persona ?? 'S/C',
                        'nombre_completo' => $matricula->alumno->nombre_completo ?? '',
                        'notas' => $notasPorMateria,
                        'promedio_general' => $promedioGeneral,
                        'reprobadas_count' => $materiasReprobadas,
                        'detalle_reprobadas' => $detalleReprobadas,
                    ]);
                }
            }

            return view('academico.reportes.mi-aula', compact(
                'anio',
                'aulaGuia',
                'cortes',
                'corteId',
                'corteSeleccionado',
                'asignaciones',
                'alumnosReporte',
                'tipoReporte',
                'filtroRiesgo',
                'materiaId',
                'todasLasAsignaciones'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el reporte de tu aula.'));
        }
    }
}