<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaAula;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Boletin;
use App\Models\CorteEvaluativo;
use App\Models\EvaluacionFamiliar;
use App\Models\GrupoMateria;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Usuario;
use App\Services\NotaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoletinController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected NotaService $notaService
    ) {}

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Boletin::class);
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $aulas = $this->getAulasScope($usuario);

            if ($aulas->isEmpty()) {
                return view('academico.boletin.index', [
                    'aulas' => collect(),
                    'aulaSeleccionada' => null,
                    'aulaActual' => null,
                    'matriculas' => collect(),
                    'todosAprobados' => false,
                ]);
            }

            $aulaSeleccionada = $request->query('aula_id', $aulas->first()->id);
            $aulaActual = $aulas->firstWhere('id', $aulaSeleccionada);
            $matriculas = collect();
            $todosAprobados = false;

            if ($aulaSeleccionada) {
                $anioEscolarId = Aula::where('id', $aulaSeleccionada)->value('anio_escolar_id');
                $hoy = now()->timezone('America/Managua')->toDateString();

                $corteActivo = CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)
                    ->where('fecha_inicio', '<=', $hoy)
                    ->where('fecha_fin', '>=', $hoy)
                    ->first() ?? CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)->orderByDesc('numero')->first();

                $matriculas = Matricula::with(['alumno', 'boletines' => function ($q) use ($corteActivo) {
                    if ($corteActivo) {
                        $q->where('corte_evaluativo_id', $corteActivo->id);
                    }
                }])
                    ->where('aula_id', $aulaSeleccionada)
                    ->where('estado', 'activo')
                    ->orderBy('id')
                    ->get();

                if ($matriculas->isNotEmpty() && $corteActivo) {
                    $todosAprobados = $matriculas->every(fn ($m) => $m->boletines->isNotEmpty());
                }
            }

            return view('academico.boletin.index', compact('aulas', 'aulaSeleccionada', 'aulaActual', 'matriculas', 'todosAprobados'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar el panel de boletines.'));
        }
    }

    public function aprobarBoletin(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('update', $matricula);

            $anioEscolarId = $matricula->anio_escolar_id;
            $hoy = now()->timezone('America/Managua')->toDateString();

            $corteActivo = CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)
                ->where('fecha_inicio', '<=', $hoy)
                ->where('fecha_fin', '>=', $hoy)
                ->first() ?? CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)->orderByDesc('numero')->first();

            if (!$corteActivo) {
                return back()->with('error', 'No hay un corte evaluativo activo para validar calificaciones.');
            }

            $asignacionesIds = AulaAsignaturaDocente::where('aula_id', $matricula->aula_id)
                ->where('anio_escolar_id', $anioEscolarId)
                ->pluck('id');

            $notasRegistradas = Nota::where('matricula_id', $matricula->id)
                ->where('corte_evaluativo_id', $corteActivo->id)
                ->whereIn('aula_asignatura_docente_id', $asignacionesIds)
                ->where(function ($q) {
                    $q->whereNotNull('nota_cuantitativa')
                        ->orWhereNotNull('indicador_logro_id')
                        ->orWhereNotNull('porcentaje_avance');
                })
                ->pluck('aula_asignatura_docente_id');

            if ($notasRegistradas->count() < $asignacionesIds->count()) {
                return back()->with('error', 'No se puede dar el Visto Bueno. El estudiante tiene asignaturas pendientes en este corte.');
            }

            Boletin::updateOrCreate(
                [
                    'matricula_id' => $matricula->id,
                    'corte_evaluativo_id' => $corteActivo->id,
                ],
                [
                    'fecha_generacion' => now(),
                    'archivo_path' => 'aprobado_por_tutor',
                ]
            );

            return back()->with('success', 'Boletín verificado y guardado en la caja del aula correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Hubo un problema al intentar validar y aprobar el boletín.'));
        }
    }

    public function show(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('view', $matricula);

            $matricula->load([
                'alumno',
                'aula.grado',
                'aula.modalidad',
                'aula.docenteGuia.usuario',
                'anioEscolar',
            ]);

            $anioEscolarId = $matricula->anio_escolar_id;
            $cortes = CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)->orderBy('numero')->get();
            $hoy = now()->timezone('America/Managua')->toDateString();

            $corteActual = $request->query('corte_evaluativo_id')
                ? $cortes->firstWhere('id', (int) $request->query('corte_evaluativo_id'))
                : ($cortes->first(fn ($c) => $c->fecha_inicio <= $hoy && $c->fecha_fin >= $hoy) ?? $cortes->last());

            $numeroActual = $corteActual->numero ?? 1;

            $asignaciones = AulaAsignaturaDocente::with('asignatura.grupoMateria')
                ->where('aula_id', $matricula->aula_id)
                ->where('anio_escolar_id', $anioEscolarId)
                ->get();

            $cortePorNumero = $cortes->keyBy('numero');
            $areas = [];
            $acumuladoCortes = [1 => [], 2 => [], 3 => [], 4 => []];

            foreach ($asignaciones as $asignacion) {
                $resumen = $this->notaService->calcularResumenAsignatura($matricula, $asignacion);
                $cortesData = [1 => null, 2 => null, 3 => null, 4 => null];

                foreach ([1, 2, 3, 4] as $numero) {
                    if (isset($resumen['cortes'][$numero]) && $resumen['cortes'][$numero] !== null) {
                        $cuan = (float) $resumen['cortes'][$numero];
                        $cua = $this->notaService->calcularIndicadorLogro((int) round($cuan));
                        $cortesData[$numero] = ['cua' => $cua, 'cuan' => number_format($cuan, 0)];
                        $acumuladoCortes[$numero][] = $cuan;
                    }
                }

                $finalCuan = $resumen['nota_final'];
                $final = ($finalCuan !== null)
                    ? ['cua' => $resumen['indicador_final'], 'cuan' => number_format($finalCuan, 0)]
                    : null;

                $area = $asignacion->asignatura->grupoMateria->nombre ?? 'Otros';
                $areas[$area][] = [
                    'nombre' => $asignacion->asignatura->nombre,
                    'cortes' => $cortesData,
                    'final' => $final,
                    'aprobado' => $resumen['aprobado'],
                ];
            }

            $ordenGrupos = GrupoMateria::pluck('orden', 'nombre')->all();
            uksort($areas, fn ($a, $b) => ($ordenGrupos[$a] ?? 999) <=> ($ordenGrupos[$b] ?? 999));

            $promedios = [];
            foreach ([1, 2, 3, 4] as $numero) {
                $valores = $acumuladoCortes[$numero];
                if ($valores !== []) {
                    $promCuan = round(array_sum($valores) / count($valores), 2);
                    $promedios[$numero] = [
                        'cua' => $this->notaService->calcularIndicadorLogro((int) round($promCuan)),
                        'cuan' => number_format($promCuan, 0),
                    ];
                } else {
                    $promedios[$numero] = null;
                }
            }

            $asistencia = [];
            foreach ([1, 2, 3, 4] as $numero) {
                if ($numero <= $numeroActual) {
                    $corte = $cortePorNumero->get($numero);
                    $query = AsistenciaAula::where('matricula_id', $matricula->id);
                    if ($corte) {
                        $query->whereBetween('fecha', [$corte->fecha_inicio, $corte->fecha_fin]);
                    }
                    $registros = $query->get();

                    $asistencia[$numero] = [
                        'injustificadas' => $registros->where('estado_asistencia', 'Ausencia Injustificada')->count(),
                        'justificadas' => $registros->where('estado_asistencia', 'Ausencia Justificada')->count(),
                    ];
                } else {
                    $asistencia[$numero] = ['injustificadas' => '', 'justificadas' => ''];
                }
            }

            $compromiso = [];
            $evaluacionesReales = EvaluacionFamiliar::where('matricula_id', $matricula->id)->get();
            foreach ([1, 2, 3, 4] as $numero) {
                if ($numero <= $numeroActual) {
                    $corte = $cortePorNumero->get($numero);
                    $eval = $corte ? $evaluacionesReales->firstWhere('corte_evaluativo_id', $corte->id) : null;
                    $compromiso[$numero] = $eval ? $eval->evaluacion : '—';
                } else {
                    $compromiso[$numero] = '';
                }
            }

            return view('academico.boletin.show', compact(
                'matricula',
                'areas',
                'promedios',
                'asistencia',
                'compromiso',
                'corteActual'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'Ocurrió un error al generar el boletín.'));
        }
    }

    public function constancia(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('view', $matricula);
            $matricula->load(['alumno', 'aula.grado', 'aula.modalidad', 'aula.docenteGuia.usuario', 'anioEscolar']);

            $cortes = CorteEvaluativo::where('anio_escolar_id', $matricula->anio_escolar_id)->orderBy('numero')->get();
            $asignaciones = AulaAsignaturaDocente::with('asignatura.grupoMateria')
                ->where('aula_id', $matricula->aula_id)
                ->where('anio_escolar_id', $matricula->anio_escolar_id)
                ->get();

            $filas = [];
            $promediosFinales = [];

            foreach ($asignaciones as $asignacion) {
                $resumen = $this->notaService->calcularResumenAsignatura($matricula, $asignacion);
                $cortesValores = [];
                foreach ($cortes as $corte) {
                    $cortesValores[$corte->numero] = $resumen['cortes'][$corte->numero] ?? null;
                }

                $filas[] = [
                    'asignatura' => $asignacion->asignatura->nombre,
                    'area' => $asignacion->asignatura->grupoMateria->nombre ?? 'Otros',
                    'cortes' => $cortesValores,
                    'nota_final' => $resumen['nota_final'],
                    'indicador' => $resumen['indicador_final'],
                    'aprobado' => $resumen['aprobado'],
                ];

                if ($resumen['nota_final'] !== null) {
                    $promediosFinales[] = $resumen['nota_final'];
                }
            }

            $promedioGeneral = $promediosFinales !== []
                ? round(array_sum($promediosFinales) / count($promediosFinales), 2)
                : null;

            return view('academico.boletin.constancia', compact('matricula', 'cortes', 'filas', 'promedioGeneral'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo generar la constancia de notas.'));
        }
    }

    protected function getAulasScope(Usuario $usuario)
    {
        $query = Aula::with(['grado', 'anioEscolar'])
            ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'));

        if ($usuario->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'secretaria', 'coordinador', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Secretaria', 'Coordinador'])) {
            return $query->get();
        }

        $docente = $usuario->docente;
        if (!$docente) {
            return collect();
        }

        if ($usuario->hasAnyRole(['docente_guia', 'Docente Guia'])) {
            return $query->where('docente_guia_id', $docente->id)->get();
        }

        if ($usuario->hasAnyRole(['docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'Docente por Asignatura'])) {
            return $query->whereHas('asignaciones', fn ($q) => $q->where('docente_id', $docente->id)->where('activo', true))->get();
        }

        return collect();
    }
}