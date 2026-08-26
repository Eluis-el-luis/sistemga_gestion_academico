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
     * Helper privado para no repetir la consulta a la BD
     */
    private function getAulasPaginadas()
    {
        return Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                     ->orderBy('anio_escolar_id', 'desc')
                     ->orderBy('grado_id')
                     ->paginate(15);
    }

    /**
     * 1. ACCESO: Gestión de Aulas
     */
    public function index()
    {
        $this->authorize('viewAny', Aula::class);
        $aulas = $this->getAulasPaginadas();
        $contexto = 'gestion'; // Le decimos a la vista de dónde venimos

        return view('academico.aulas.index', compact('aulas', 'contexto'));
    }

    /**
     * 2. ACCESO: Asignación de Maestros
     */
    public function indexAsignaciones()
    {
        $this->authorize('viewAny', Aula::class);
        $aulas = $this->getAulasPaginadas();
        $contexto = 'asignacion'; 

        return view('academico.aulas.index', compact('aulas', 'contexto'));
    }

    /**
     * 3. ACCESO: Gestor de Horarios
     */
    public function indexHorarios()
    {
        // Nota: Asumo que si pueden ver aulas, pueden ver esta vista.
        $this->authorize('viewAny', Aula::class); 
        $aulas = $this->getAulasPaginadas();
        $contexto = 'horarios'; 

        return view('academico.aulas.index', compact('aulas', 'contexto'));
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
        
        $aniosActivosIds = $anios->pluck('id');
        $docentesOcupados = Aula::whereIn('anio_escolar_id', $aniosActivosIds)
                                ->pluck('docente_guia_id')
                                ->toArray();

        $docentes = \App\Models\Docente::with('usuario')
                        ->whereNotIn('id', $docentesOcupados)
                        ->get();

        return view('academico.aulas.create', compact('modalidades', 'grados', 'anios', 'docentes'));
    }

    public function store(StoreAulaRequest $request)
    {
        $this->authorize('create', Aula::class);

        $this->aulaService->crearAulaConMalla($request->validated());

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Aula creada exitosamente. Las asignaturas de la malla curricular han sido asignadas automáticamente.');
    }

    public function edit(Aula $aula)
    {
        $this->authorize('update', $aula);

        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        $anios = \App\Models\AnioEscolar::all();
        
        $docentesOcupados = Aula::where('anio_escolar_id', $aula->anio_escolar_id)
                                ->where('id', '!=', $aula->id)
                                ->pluck('docente_guia_id')
                                ->toArray();

        $docentes = \App\Models\Docente::with('usuario')
                                ->whereNotIn('id', $docentesOcupados)
                                ->get();

        return view('academico.aulas.edit', compact('aula', 'modalidades', 'grados', 'anios', 'docentes'));
    }

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

    public function destroy(Aula $aula)
    {
        $this->authorize('delete', $aula);

        if ($aula->matriculas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el aula porque tiene estudiantes matriculados.');
        }

        $aula->delete();

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'El aula ha sido eliminada del sistema correctamente.');
    }

    public function show(Aula $aula)
    {
        $this->authorize('update', $aula);
        $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

        $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        $todasAsignaturas = \App\Models\Asignatura::all();
        $todosDocentes = \App\Models\Docente::with('usuario')->get();

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes'));
    }
}