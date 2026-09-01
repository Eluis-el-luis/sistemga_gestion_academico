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

    public function __construct(AulaService $aulaService)
    {
        $this->aulaService = $aulaService;
    }

    private function getAulasPaginadas()
    {
        return Aula::with(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario'])
                     ->orderBy('anio_escolar_id', 'desc')
                     ->orderBy('grado_id')
                     ->paginate(15);
    }

    public function index()
    {
        $this->authorize('viewAny', Aula::class);
        $aulas = $this->getAulasPaginadas();
        $contexto = 'gestion'; 

        return view('academico.aulas.index', compact('aulas', 'contexto'));
    }

    public function indexAsignaciones()
    {
        $this->authorize('viewAny', Aula::class);
        $aulas = $this->getAulasPaginadas();
        $contexto = 'asignacion'; 

        return view('academico.aulas.index', compact('aulas', 'contexto'));
    }

    public function indexHorarios()
    {
        $this->authorize('viewAny', Aula::class); 
        $aulas = $this->getAulasPaginadas();
        $contexto = 'horarios'; 

        return view('academico.aulas.index', compact('aulas', 'contexto'));
    }

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
        $datos = $request->validated();

        // 🔒 FILTRO ANTI-DUPLICADOS (REGLA DE NEGOCIO ESTRICTA)
        $existeDuplicado = Aula::where('anio_escolar_id', $datos['anio_escolar_id'])
            ->where('grado_id', $datos['grado_id'])
            ->where('nombre', $datos['nombre']) // Identificador de la sección (A, B, etc.)
            ->where('turno', $datos['turno'])
            ->exists();

        if ($existeDuplicado) {
            return back()->withInput()->with('error', '¡Bloqueo de seguridad! Ya existe un aula aperturada con esa misma combinación de Año Escolar, Grado, Sección y Turno.');
        }

        $this->aulaService->crearAulaConMalla($datos);

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Aula creada exitosamente. Las asignaturas de la malla curricular han sido asignadas automáticamente.');
    }

    public function edit(Aula $aula)
    {
        $this->authorize('update', $aula);

        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        // Tomamos el año escolar activo por defecto
        $anios = \App\Models\AnioEscolar::where('activo', true)->get();
        
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

        // Exigimos que el docente guía sea OBLIGATORIO (required)
        $validated = $request->validate([
            'anio_escolar_id' => 'required|exists:anio_escolar,id',
            'docente_guia_id' => 'required|exists:docente,id',
        ]);

        $aula->update($validated);

        return redirect()->route('academico.aulas.index')
                         ->with('success', 'Asignación del aula actualizada exitosamente.');
    }
    public function destroy(\App\Models\Aula $aula)
    {
        $this->authorize('delete', $aula);

        try {
            $aula->delete();
            return back()->with('success', 'Aula eliminada correctamente.');
            
        } catch (\Illuminate\Database\QueryException $e) {
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

        $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
        $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();

        $todosDocentes = \App\Models\Docente::with('usuario')->get();
        $contexto = 'gestion'; 

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes', 'contexto'));
    }

    public function showAsignaciones(Aula $aula)
    {
        $this->authorize('viewAny', Aula::class);
        $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

        $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                            ->where('aula_id', $aula->id)
                            ->get();

        $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
        $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();

        $todosDocentes = \App\Models\Docente::with('usuario')->get();
        $contexto = 'asignacion'; 

        return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes', 'contexto'));
    }
}