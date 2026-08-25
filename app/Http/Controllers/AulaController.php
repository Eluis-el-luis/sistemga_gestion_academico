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
     * Muestra el formulario para editar un aula existente.
     */
    public function edit(Aula $aula)
    {
        $this->authorize('update', $aula);

        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        $anios = \App\Models\AnioEscolar::all();
        
        // Obtenemos los docentes ocupados en el año del aula, EXCLUYENDO al docente actual de esta aula
        $docentesOcupados = Aula::where('anio_escolar_id', $aula->anio_escolar_id)
                                ->where('id', '!=', $aula->id)
                                ->pluck('docente_guia_id')
                                ->toArray();

        $docentes = \App\Models\Docente::with('usuario')
                                ->whereNotIn('id', $docentesOcupados)
                                ->get();

        return view('academico.aulas.edit', compact('aula', 'modalidades', 'grados', 'anios', 'docentes'));
    }

    /**
     * Actualiza los datos del aula en la base de datos.
     */
    public function update(Request $request, Aula $aula)
    {
        $this->authorize('update', $aula);

        $validated = $request->validate([
            'anio_escolar_id' => 'required|exists:anios_escolares,id',
            'modalidad_id'    => 'required|exists:modalidades,id',
            'grado_id'        => 'required|exists:grados,id',
            'nombre'          => 'required|string|max:50',
            'turno'           => 'required|string|max:50',
            'cupo'            => 'required|integer|min:1|max:100',
            'docente_guia_id' => 'nullable|exists:docentes,id',
        ]);

        $aula->update($validated);

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Información del aula actualizada exitosamente.');
    }

    /**
     * Elimina un aula del sistema.
     */
    public function destroy(Aula $aula)
    {
        $this->authorize('delete', $aula);

        // Opcional: Validar si tiene matrículas activas antes de borrar
        if ($aula->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el aula porque tiene estudiantes matriculados.');
        }

        $aula->delete();

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'El aula ha sido eliminada del sistema correctamente.');
    }

    /**
     * Muestra la estructura interna de un aula específica (Sus materias y docentes).
     */
    public function show(Aula $aula)
    {
        $this->authorize('update', $aula);
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