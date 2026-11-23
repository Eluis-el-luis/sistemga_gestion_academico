<?php

namespace App\Http\Controllers;

use App\Models\ActividadEvaluativa;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteCerrado;
use App\Models\CorteEvaluativo;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\NotaActividad;
use App\Models\SolicitudEdicionNota;
use App\Models\Usuario;
use App\Services\NotaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected NotaService $notaService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->authorize('viewAny', Nota::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $asignaciones = $this->getAsignacionesScope($usuario, $request);

            $esDirectivo = $usuario->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']);
            $esCoordinador = $usuario->hasAnyRole(['coordinador', 'Coordinador']);
            $modoSupervision = $esDirectivo || $esCoordinador;

            $asignacionesPorAula = [];

            if ($esDirectivo) {
                $grados = Grado::with('modalidad')->orderBy('modalidad_id', 'asc')->orderBy('id', 'asc')->get();
                $aulas = Aula::with('grado')->get();
                $gradoSeleccionadoId = $request->filled('grado_id') ? $request->input('grado_id') : ($grados->first()->id ?? null);
                $aulaSeleccionadaId = $request->filled('aula_id') ? $request->input('aula_id') : ($aulas->where('grado_id', $gradoSeleccionadoId)->first()->id ?? null);
            } elseif ($esCoordinador) {
                $docente = $usuario->docente;
                $aulas = Aula::with(['grado', 'asignaciones.asignatura', 'asignaciones.docente.usuario'])
                    ->where('modalidad_id', $docente?->modalidad_coordina_id)
                    ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                    ->orderBy('grado_id')
                    ->get();

                foreach ($aulas as $aula) {
                    $asignacionesPorAula[$aula->id] = $aula->asignaciones
                        ->where('activo', true)
                        ->sortBy('asignatura.nombre')
                        ->values();
                }

                $grados = collect();
                $gradoSeleccionadoId = null;
                $aulaSeleccionadaId = $aulas->first()->id ?? null;
            } else {
                $grados = collect();
                $aulas = collect();
                $gradoSeleccionadoId = null;
                $aulaSeleccionadaId = null;
            }

            return view('academico.notas.index', compact(
                'asignaciones',
                'modoSupervision',
                'grados',
                'aulas',
                'gradoSeleccionadoId',
                'aulaSeleccionadaId',
                'esCoordinador',
                'asignacionesPorAula'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar el panel de calificaciones.'));
        }
    }

    protected function getAsignacionesScope(Usuario $usuario, Request $request)
    {
        $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario']);

        if ($usuario->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            if ($request->filled('aula_id')) {
                $query->where('aula_id', (int) $request->input('aula_id'));
            }

            return $query->get();
        }

        $docente = $usuario->docente;
        if (!$docente) {
            return collect();
        }

        if ($usuario->hasAnyRole(['docente_guia', 'Docente Guia', 'Docente Guía'])) {
            return $query->where(function ($q) use ($docente) {
                $q->where('docente_id', $docente->id)
                    ->orWhereHas('aula', fn ($aq) => $aq->where('docente_guia_id', $docente->id));
            })->get();
        }

        if ($usuario->hasAnyRole(['coordinador', 'Coordinador'])) {
            if ($docente->modalidad_coordina_id) {
                return $query->whereHas('aula', fn ($q) => $q->where('modalidad_id', $docente->modalidad_coordina_id))->get();
            }

            return collect();
        }

        return $query->where('docente_id', $docente->id)->get();
    }

    protected function isCorteBloqueado(int $corteId, int $asignacionId): bool
    {
        return CorteCerrado::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->where('bloqueado', true)
            ->exists();
    }

    public function create(Request $request, AulaAsignaturaDocente $asignacion): View|RedirectResponse
    {
        return $this->cargarVistaPlanilla($request, $asignacion, 'academico.notas.planilla');
    }

    public function evaluar(Request $request, AulaAsignaturaDocente $asignacion): View|RedirectResponse
    {
        return $this->cargarVistaPlanilla($request, $asignacion, 'academico.notas.evaluar');
    }

    protected function cargarVistaPlanilla(Request $request, AulaAsignaturaDocente $asignacion, string $vista): View|RedirectResponse
    {
        try {
            $this->authorize('calificar', $asignacion);
            $asignacion->load('aula.grado', 'asignatura');

            $cortes = CorteEvaluativo::with('anioEscolar')
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->orderBy('numero')
                ->get();

            if ($cortes->isEmpty()) {
                return view($vista, [
                    'asignacion' => $asignacion,
                    'cortes' => collect(),
                    'corteSeleccionado' => null,
                    'matriculas' => collect(),
                    'actividades' => collect(),
                    'notasActividades' => collect(),
                    'estaBloqueado' => false,
                    'corteActivo' => null,
                    'sumaAcumulado' => 0,
                    'sumaExamen' => 0,
                    'pesoAcumulado' => 0,
                    'pesoExamen' => 0,
                ]);
            }

            $corteSeleccionado = $request->query('corte_evaluativo_id', $cortes->first()->id);

            $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corteSeleccionado)
                ->orderBy('fecha', 'asc')
                ->get();

            $estaBloqueado = $this->isCorteBloqueado((int) $corteSeleccionado, (int) $asignacion->id);

            $matriculas = Matricula::with([
                'alumno',
                'notas' => function ($query) use ($asignacion, $corteSeleccionado) {
                    $query->where('aula_asignatura_docente_id', $asignacion->id)
                        ->where('corte_evaluativo_id', $corteSeleccionado);
                },
            ])
                ->where('aula_id', $asignacion->aula_id)
                ->where('estado', 'activo')
                ->get()
                ->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');

            $notasActividades = NotaActividad::whereIn('matricula_id', $matriculas->pluck('id'))
                ->whereIn('actividad_evaluativa_id', $actividades->pluck('id'))
                ->get()
                ->groupBy('matricula_id');

            $corteActivo = $cortes->firstWhere('id', $corteSeleccionado);
            $sumaAcumulado = $actividades->where('tipo', 'acumulado')->sum('puntaje_maximo');
            $sumaExamen = $actividades->where('tipo', 'examen')->sum('puntaje_maximo');
            $pesoAcumulado = $corteActivo?->peso_acumulado ?? 0;
            $pesoExamen = $corteActivo?->peso_examen ?? 0;

            return view($vista, compact(
                'asignacion',
                'cortes',
                'corteSeleccionado',
                'matriculas',
                'actividades',
                'notasActividades',
                'estaBloqueado',
                'corteActivo',
                'sumaAcumulado',
                'sumaExamen',
                'pesoAcumulado',
                'pesoExamen'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.notas.index')
                ->with('error', $this->formatearError($e, 'No fue posible cargar la planilla de calificaciones.'));
        }
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion): RedirectResponse
    {
        $corteId = $request->input('corte_evaluativo_id');

        if (!$corteId || !CorteEvaluativo::where('id', $corteId)->exists()) {
            return back()->withInput()->with('error', 'Debe seleccionar un periodo evaluativo válido.');
        }

        if (!$request->has('notas') || !is_array($request->input('notas'))) {
            return back()->withInput()->with('error', 'No se recibieron calificaciones para guardar.');
        }

        try {
            $this->authorize('calificar', $asignacion);

            if ($this->isCorteBloqueado((int) $corteId, (int) $asignacion->id)) {
                return back()->withInput()
                    ->with('error', 'El parcial está cerrado. Solicita autorización para modificar.');
            }

            $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                ->where('corte_evaluativo_id', $corteId)
                ->get()
                ->keyBy('id');

            $corte = CorteEvaluativo::find($corteId);
            $totalConfigurado = (float) $actividades->sum('puntaje_maximo');
            $totalPosible = (float) (($corte?->peso_acumulado ?? 0) + ($corte?->peso_examen ?? 0));
            if ($totalPosible <= 0) {
                $totalPosible = $totalConfigurado;
            }

            $actorId = Auth::id();

            DB::transaction(function () use ($request, $asignacion, $corteId, $actividades, $totalPosible, $actorId) {
                foreach ($request->input('notas') as $matriculaId => $calificaciones) {
                    if (!is_array($calificaciones)) {
                        continue;
                    }

                    $sumaTotalAlumno = 0.0;

                    foreach ($calificaciones as $actividadId => $notaIngresada) {
                        if (is_null($notaIngresada) || $notaIngresada === '') {
                            continue;
                        }

                        $actividad = $actividades->get($actividadId);
                        if (!$actividad) {
                            continue;
                        }

                        $notaFinal = min(abs((float) $notaIngresada), (float) $actividad->puntaje_maximo);

                        NotaActividad::updateOrCreate(
                            [
                                'matricula_id' => (int) $matriculaId,
                                'actividad_evaluativa_id' => (int) $actividadId,
                            ],
                            [
                                'nota_obtenida' => $notaFinal,
                                'updated_by' => $actorId,
                            ]
                        );

                        $sumaTotalAlumno += $notaFinal;
                    }

                    $this->notaService->registrarNotaFinal(
                        (int) $matriculaId,
                        (int) $asignacion->id,
                        (int) $corteId,
                        $sumaTotalAlumno,
                        $totalPosible,
                        $actorId
                    );
                }
            });

            return back()->with('success', 'Calificaciones actualizadas. La nota final del corte se ha calculado exitosamente.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible guardar las calificaciones.'));
        }
    }

    public function cerrarParcial(Request $request, AulaAsignaturaDocente $asignacion): RedirectResponse
    {
        try {
            $this->authorize('calificar', $asignacion);

            $corteId = $request->input('corte_evaluativo_id');
            if (!$corteId || !CorteEvaluativo::where('id', $corteId)->exists()) {
                return back()->with('error', 'Debe indicar un corte evaluativo válido para cerrar.');
            }

            CorteCerrado::updateOrCreate(
                [
                    'aula_asignatura_docente_id' => $asignacion->id,
                    'corte_evaluativo_id' => (int) $corteId,
                ],
                [
                    'bloqueado' => true,
                    'cerrado_por' => Auth::id(),
                    'fecha_cierre' => now(),
                ]
            );

            return back()->with('success', 'Calificaciones cerradas de forma permanente. Ya no pueden ser editadas.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible cerrar el parcial.'));
        }
    }

    public function solicitarDesbloqueo(Request $request, AulaAsignaturaDocente $asignacion): RedirectResponse
    {
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
            'corte_evaluativo_id' => ['nullable', 'integer', 'exists:corte_evaluativo,id'],
        ]);

        try {
            $this->authorize('calificar', $asignacion);

            /** @var Usuario|null $usuario */
            $usuario = Auth::user();
            $docenteId = $usuario?->docente?->id;

            if (!$docenteId) {
                return back()->with('error', 'Su usuario no tiene un perfil de docente asociado.');
            }

            $notaReferencia = Nota::where('aula_asignatura_docente_id', $asignacion->id)
                ->when(!empty($validated['corte_evaluativo_id']), fn ($q) => $q->where('corte_evaluativo_id', (int) $validated['corte_evaluativo_id']))
                ->first();

            if ($notaReferencia) {
                SolicitudEdicionNota::updateOrCreate(
                    [
                        'docente_id' => $docenteId,
                        'nota_id' => $notaReferencia->id,
                        'estado' => 'Pendiente',
                    ],
                    [
                        'motivo' => $validated['motivo'],
                    ]
                );
            }

            return back()->with('success', 'Solicitud enviada. Dirección o Subdirección revisará tu petición pronto.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('error', $this->formatearError($e, 'No fue posible enviar la solicitud de desbloqueo.'));
        }
    }
}