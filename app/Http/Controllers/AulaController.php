<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Http\Requests\StoreAulaRequest;
use App\Services\AulaService; 
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AulaController extends Controller
{
    use AuthorizesRequests;

    protected $aulaService;

    // Inyectamos el servicio en el constructor
    public function __construct(AulaService $aulaService)
    {
        $this->aulaService = $aulaService;
    }

    /**
     * Muestra el listado de aulas.
     */
    public function index()
    {
        $this->authorize('viewAny', Aula::class);

        // Traemos las aulas con sus relaciones principales
        $aulas = Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                     ->orderBy('anio_escolar_id', 'desc')
                     ->orderBy('grado_id')
                     ->paginate(15);

        return view('academico.aulas.index', compact('aulas'));
    }

    /**
     * Muestra el formulario para crear una nueva aula.
     */
    public function create()
    {
        $this->authorize('create', Aula::class);

        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        $anios = \App\Models\AnioEscolar::where('activo', true)->get();
        
        // 1. Obtenemos los IDs de los docentes que YA SON guías en los años activos
        $aniosActivosIds = $anios->pluck('id');
        $docentesOcupados = Aula::whereIn('anio_escolar_id', $aniosActivosIds)
                                ->pluck('docente_guia_id')
                                ->toArray();

        // 2. Traemos SOLO a los docentes que NO están en la lista de ocupados
        $docentes = \App\Models\Docente::with('usuario')
                        ->whereNotIn('id', $docentesOcupados)
                        ->get();

        return view('academico.aulas.create', compact('modalidades', 'grados', 'anios', 'docentes'));
    }

    public function store(StoreAulaRequest $request)
    {
        $this->authorize('create', Aula::class);

        // Llamamos al servicio en lugar de usar Aula::create()
        // El servicio guarda el aula Y le inyecta las materias automáticamente
        $this->aulaService->crearAulaConMalla($request->validated());

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Aula creada exitosamente. Las asignaturas de la malla curricular han sido asignadas automáticamente.');
    }

    /**
     * Muestra la estructura interna de un aula específica (Sus materias y docentes).
     */
    public function show(Aula $aula)
    {
        $this->authorize('viewAny', Aula::class);
        $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

        $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        // 1. Traemos TODAS las asignaturas para el botón "Agregar Materia Extra"
        $todasAsignaturas = \App\Models\Asignatura::all();
        
        // 2. Traemos a TODOS los docentes para el botón "Asignar Profesor"
        $todosDocentes = \App\Models\Docente::with('usuario')->get();

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes'));
    }
}