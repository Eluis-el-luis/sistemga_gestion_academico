<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAulaRequest;
use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\BloqueHorario;
use App\Models\CorteEvaluativo;
use App\Models\Docente;
use App\Models\Grado;
use App\Models\MallaCurricular;
use App\Models\Modalidad;
use App\Models\Nota;
use App\Models\Usuario;
use App\Services\AulaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AulaController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected AulaService $aulaService
    ) {}

    private function getAulasPaginadas()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $query = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
            ->orderByDesc('anio_escolar_id')
            ->orderBy('grado_id');

        if ($user->hasAnyRole(['coordinador', 'Coordinador']) && !$user->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios'])) {
            $docente = $user->docente;
            if ($docente && $docente->modalidad_coordina_id) {
                $query->where('modalidad_id', $docente->modalidad_coordina_id);
            }
        }

        return $query->paginate(15);
    }

    public function destroyAsignatura($aulaId, $asignacionId)
    {
        try {
            $aula = Aula::findOrFail($aulaId);
            $this->authorize('update', $aula);

            $asignacion = AulaAsignaturaDocente::where('aula_id', $aula->id)->findOrFail($asignacionId);
            $asignacion->delete();

            return back()->with('success', 'Asignatura eliminada de la carga horaria.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar la asignatura de la carga horaria.'));
        }
    }

    public function index()
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aulas = $this->getAulasPaginadas();

            return view('academico.aulas.index', compact('aulas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar el listado de aulas.'));
        }
    }

    public function show(Aula $aula)
    {
        try {
            $this->authorize('view', $aula);

            $aula->load([
                'grado',
                'modalidad',
                'anioEscolar',
                'docenteGuia.usuario',
                'matriculas.alumno',
            ]);

            $matriculas = $aula->matriculas->sortBy(fn ($m) => $m->alumno->nombre_completo ?? '');
            $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario', 'horarios.bloqueHorario'])
                ->where('aula_id', $aula->id)
                ->orderBy('id')
                ->get();

            $bloques = BloqueHorario::where('modalidad_id', $aula->modalidad_id)
                ->where('turno', $aula->turno)
                ->where('tipo_jornada', 'Regular')
                ->orderBy('hora_inicio')
                ->get();

            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            $diasBD = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

            $horarios = $asignaciones->pluck('horarios')->flatten();
            $horariosIndex = [];
            foreach ($horarios as $h) {
                $horariosIndex[$h->bloque_horario_id][$h->dia_semana] = $h;
            }

            $matriz = [];
            foreach ($bloques as $bloque) {
                $fila = ['bloque' => $bloque, 'dias' => []];
                foreach ($dias as $i => $dia) {
                    $diaBD = $diasBD[$i];
                    $fila['dias'][$dia] = $bloque->es_recreo ? null : ($horariosIndex[$bloque->id][$diaBD] ?? null);
                }
                $matriz[] = $fila;
            }

            $matriculaIds = $matriculas->pluck('id');
            $cortes = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderBy('numero')->get();

            $evaluacionesPorCorte = [];
            foreach ($cortes as $corte) {
                $notasCorte = Nota::whereIn('matricula_id', $matriculaIds)
                    ->where('corte_evaluativo_id', $corte->id)
                    ->get();

                $evaluacionesPorCorte[] = [
                    'corte' => $corte,
                    'total_alumnos' => $matriculaIds->count(),
                    'alumnos_con_nota' => $notasCorte->count(),
                    'promedio' => $notasCorte->count() > 0 ? round((float) $notasCorte->avg('nota_cuantitativa'), 1) : null,
                    'aprobados' => $notasCorte->where('nota_cuantitativa', '>=', 60)->count(),
                    'reprobados' => $notasCorte->where('nota_cuantitativa', '<', 60)->count(),
                ];
            }

            $horasTotales = $asignaciones->where('activo', true)->sum('horas_semanales');

            return view('academico.aulas.show', compact(
                'aula',
                'matriculas',
                'asignaciones',
                'matriz',
                'dias',
                'diasBD',
                'bloques',
                'cortes',
                'evaluacionesPorCorte',
                'horasTotales'
            ));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.aulas.index')->with('error', $this->formatearError($e, 'No se pudo cargar la información completa de esta aula.'));
        }
    }

    public function create()
    {
        try {
            $this->authorize('create', Aula::class);

            $modalidades = Modalidad::all();
            $grados = Grado::all();
            $anios = AnioEscolar::orderByDesc('nombre')->get();
            $aniosActivosIds = AnioEscolar::where('estado', 'activo')->pluck('id');
            $docentesOcupados = Aula::whereIn('anio_escolar_id', $aniosActivosIds)->pluck('docente_guia_id')->all();
            $docentes = Docente::with('usuario')->whereNotIn('id', $docentesOcupados)->get();

            return view('academico.aulas.create', compact('modalidades', 'grados', 'anios', 'docentes'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.aulas.index')->with('error', $this->formatearError($e, 'Ocurrió un problema al abrir el formulario de apertura de aulas.'));
        }
    }

    public function store(StoreAulaRequest $request)
    {
        $datos = $request->validated();

        try {
            $this->authorize('create', Aula::class);

            $existeDuplicado = Aula::where('anio_escolar_id', $datos['anio_escolar_id'])
                ->where('grado_id', $datos['grado_id'])
                ->where('nombre', $datos['nombre'])
                ->where('turno', $datos['turno'])
                ->exists();

            if ($existeDuplicado) {
                return back()->withInput()->with('error', 'Ya existe un aula aperturada con esa misma combinación de Año Escolar, Grado, Sección y Turno.');
            }

            $this->aulaService->crearAulaConMalla($datos);

            return redirect()->route('academico.aulas.index')
                ->with('success', 'Aula creada exitosamente. Las asignaturas de la malla curricular han sido asignadas automáticamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error inesperado al intentar crear el aula.'));
        }
    }

    public function edit(Aula $aula)
    {
        try {
            $this->authorize('update', $aula);

            $modalidades = Modalidad::all();
            $grados = Grado::all();
            $anios = AnioEscolar::where('estado', 'activo')->get();
            $docentesOcupados = Aula::where('anio_escolar_id', $aula->anio_escolar_id)
                ->where('id', '!=', $aula->id)
                ->pluck('docente_guia_id')
                ->all();
            $docentes = Docente::with('usuario')->whereNotIn('id', $docentesOcupados)->get();

            return view('academico.aulas.edit', compact('aula', 'modalidades', 'grados', 'anios', 'docentes'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.aulas.index')->with('error', $this->formatearError($e, 'No se pudo cargar la información para editar esta aula.'));
        }
    }

    public function update(Request $request, Aula $aula)
    {
        $validated = $request->validate([
            'grado_id' => ['required', 'integer', 'exists:grado,id'],
            'modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'nombre' => ['required', 'string', 'max:20'],
            'turno' => ['required', 'string', 'max:20'],
            'cupo' => ['required', 'integer', 'min:1', 'max:50'],
            'anio_escolar_id' => ['required', 'integer', 'exists:anio_escolar,id'],
            'docente_guia_id' => ['nullable', 'integer', 'exists:docente,id'],
        ]);

        try {
            $this->authorize('update', $aula);

            $cambioGrado = (int) $aula->grado_id !== (int) $validated['grado_id'];
            $aula->update($validated);

            if ($cambioGrado) {
                $this->aulaService->sincronizarMalla($aula);
            }

            return redirect()->route('academico.aulas.index')->with('success', 'Aula actualizada exitosamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un problema al intentar actualizar el aula.'));
        }
    }

    public function destroy(Aula $aula)
    {
        try {
            $this->authorize('delete', $aula);
            $this->aulaService->eliminarAula($aula);

            return redirect()->route('academico.aulas.index')->with('success', 'Aula eliminada correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el aula.'));
        }
    }

    public function indexAsignaciones()
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aulas = $this->getAulasPaginadas();

            $todasAulasActivas = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->orderBy('modalidad_id')
                ->orderBy('grado_id')
                ->orderBy('nombre')
                ->get();

            $asignaturas = Asignatura::orderBy('nombre')->get();
            $docentes = Docente::with('usuario')->get()->sortBy(fn ($d) => $d->usuario->nombre_completo ?? '');
            $modalidades = Modalidad::orderBy('id')->get();
            $mallaPorAsignatura = MallaCurricular::select('asignatura_id', 'grado_id')
                ->get()
                ->groupBy('asignatura_id')
                ->map(fn ($items) => $items->pluck('grado_id')->values());

            return view('academico.asignaciones.index', compact(
                'aulas',
                'todasAulasActivas',
                'asignaturas',
                'docentes',
                'modalidades',
                'mallaPorAsignatura'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Hubo un error al intentar cargar el panel de asignaciones.'));
        }
    }

    public function showAsignaciones(Aula $aula)
    {
        try {
            $this->authorize('view', $aula);
            $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

            $asignaciones = AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                ->where('aula_id', $aula->id)
                ->get();

            $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->all();
            $todasAsignaturas = Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();
            $todosDocentes = Docente::with('usuario')->get();

            return view('academico.asignaciones.show', compact('aula', 'asignaciones', 'todosDocentes', 'todasAsignaturas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('academico.asignaciones.index')->with('error', $this->formatearError($e, 'No pudimos cargar los detalles de asignación para esta aula.'));
        }
    }

    public function indexHorarios()
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aulas = $this->getAulasPaginadas();

            return view('academico.gestor-horarios.index', compact('aulas'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error inesperado al intentar abrir el Gestor de Horarios.'));
        }
    }
}