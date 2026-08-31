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

        // Validamos ÚNICAMENTE los campos permitidos para edición
        $validated = $request->validate([
            'anio_escolar_id' => 'required|exists:anio_escolar,id',
            'docente_guia_id' => 'nullable|exists:docente,id',
        ]);

        // Los campos como grado_id, modalidad_id, nombre, turno y cupo 
        // quedan completamente protegidos contra modificaciones.
        $aula->update($validated);

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Asignación del aula actualizada exitosamente.');
    }

    /**
     * Elimina un aula del sistema.
     */
    public function destroy(\App\Models\Aula $aula)
    {
        // CORRECCIÓN: El permiso debe ser 'delete', no 'create'
        $this->authorize('delete', $aula);

        try {
            $aula->delete();
            return back()->with('success', 'Aula eliminada correctamente.');
            
        } catch (\Illuminate\Database\QueryException $e) {
            // El código 23000, 23503 indica violación de llave foránea (Integridad Referencial)
            if ($e->getCode() == 23000 || $e->getCode() == 23503) {
                return back()->with('error', 'No se puede eliminar esta aula porque tiene clases asignadas o alumnos. Elimine sus clases o traslade a los alumnos primero.');
            }
            
            return back()->with('error', 'Ocurrió un error en la base de datos al intentar eliminar el aula.');
        }
    }

    public function show(Aula $aula)
    {
        $this->authorize('update', $aula);
        $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

        $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        // --- FILTRO ANTI-DUPLICADOS ---
        $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
        $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();
        // ------------------------------

        $todosDocentes = \App\Models\Docente::with('usuario')->get();
        $contexto = 'gestion'; // Añadimos el contexto

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes', 'contexto'));
    }

    // NUEVO MÉTODO PARA ASIGNACIONES
    public function showAsignaciones(Aula $aula)
    {
        $this->authorize('viewAny', Aula::class);
        $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

        $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        // --- FILTRO ANTI-DUPLICADOS ---
        $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
        $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();
        // ------------------------------

        $todosDocentes = \App\Models\Docente::with('usuario')->get();
        $contexto = 'asignacion'; // Contexto diferente

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes', 'contexto'));
    }
}