<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Http\Requests\StoreAulaRequest;
use App\Services\AulaService; 
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\AulaAsignaturaDocente;

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

    public function destroyAsignatura($aulaId, $asignacionId)
    {
        try {
            // La barra invertida (\) fuerza a Laravel a buscar en la raíz del proyecto
            $asignacion = \App\Models\AulaAsignaturaDocente::findOrFail($asignacionId);
            $asignacion->delete();
            
            return back()->with('success', 'Asignatura eliminada de la carga horaria.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA
            return back()->with('error', 'No se pudo eliminar la asignatura. Es posible que el registro ya no exista o haya un problema de conexión.');
        }
    }

    // =========================================================================
    // 1. GESTIÓN DE AULAS (Estructura)
    // =========================================================================
    
    public function index()
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aulas = $this->getAulasPaginadas();

            return view('academico.aulas.index', compact('aulas'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el listado de aulas.');
        }
    }

    public function show(Aula $aula)
    {
        try {
            $this->authorize('update', $aula);
            $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

            $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                                ->where('aula_id', $aula->id)
                                ->get();

            $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
            $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();

            $todosDocentes = \App\Models\Docente::with('usuario')->get();

            return view('academico.aulas.show', compact('aula', 'asignaciones', 'todasAsignaturas', 'todosDocentes'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.aulas.index')->with('error', 'No se pudo cargar la estructura de esta aula. Intenta de nuevo.');
        }
    }

    public function create()
    {
        try {
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
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.aulas.index')->with('error', 'Ocurrió un problema al abrir el formulario de apertura de aulas.');
        }
    }

    public function store(StoreAulaRequest $request)
    {
        // La validación del FormRequest ya se ejecutó de forma segura antes de entrar aquí
        $datos = $request->validated();

        try {
            $this->authorize('create', Aula::class);

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
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Hubo un error inesperado al intentar crear el aula y asignar la malla curricular. Verifica los datos.');
        }
    }

    public function edit(Aula $aula)
    {
        try {
            $this->authorize('update', $aula);

            $modalidades = \App\Models\Modalidad::all();
            $grados = \App\Models\Grado::all();
            $anios = \App\Models\AnioEscolar::where('activo', true)->get();
            
            $docentesOcupados = Aula::where('anio_escolar_id', $aula->anio_escolar_id)
                                    ->where('id', '!=', $aula->id)
                                    ->pluck('docente_guia_id')
                                    ->toArray();

            $docentes = \App\Models\Docente::with('usuario')
                                    ->whereNotIn('id', $docentesOcupados)
                                    ->get();

            return view('academico.aulas.edit', compact('aula', 'modalidades', 'grados', 'anios', 'docentes'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.aulas.index')->with('error', 'No se pudo cargar la información para editar esta aula.');
        }
    }

    public function update(Request $request, Aula $aula)
    {
        // Validación AFUERA del try-catch
        $validated = $request->validate([
            'grado_id' => 'required|exists:grado,id',
            'modalidad_id' => 'required|exists:modalidad,id',
            'nombre' => 'required|string|max:20',
            'turno' => 'required|string|max:20',
            'cupo' => 'required|integer|min:1|max:50',
            'anio_escolar_id' => 'required|exists:anio_escolar,id',
            'docente_guia_id' => 'nullable|exists:docente,id',
        ]);

        try {
            $this->authorize('update', $aula);

            // Detectar si cambió el grado para re-sincronizar las asignaturas de la malla
            $cambioGrado = $aula->grado_id !== (int) $validated['grado_id'];

            $aula->update($validated);

            if ($cambioGrado) {
                $this->aulaService->sincronizarMalla($aula);
            }

            return redirect()->route('academico.aulas.index')
                             ->with('success', 'Aula actualizada exitosamente.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return back()->withInput()->with('error', 'Ocurrió un problema al intentar actualizar el aula en la base de datos.');
        }
    }

    public function destroy(\App\Models\Aula $aula)
    {
        try {
            $this->authorize('delete', $aula);
            
            $this->aulaService->eliminarAula($aula);
            return redirect()->route('academico.aulas.index')
                             ->with('success', 'Aula eliminada correctamente junto con sus asignaturas y matrículas.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // Este catch funciona perfecto como contingencia general y de integridad de BD
            return back()->with('error', 'No se pudo eliminar el aula. Asegúrate de que no existan registros protegidos asociados a ella.');
        }
    }


    // =========================================================================
    // 2. GESTOR DE ASIGNACIONES (Módulo Independiente)
    // =========================================================================

    public function indexAsignaciones()
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aulas = $this->getAulasPaginadas();

            return view('academico.asignaciones.index', compact('aulas'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Hubo un error al intentar cargar el panel de asignaciones.');
        }
    }

    public function showAsignaciones(Aula $aula)
    {
        try {
            $this->authorize('viewAny', Aula::class);
            $aula->load(['grado', 'modalidad', 'anioEscolar', 'docenteGuia.usuario']);

            $asignaciones = \App\Models\AulaAsignaturaDocente::with(['asignatura', 'docente.usuario'])
                                ->where('aula_id', $aula->id)
                                ->get();

            $asignaturasYaAsignadas = $asignaciones->pluck('asignatura_id')->toArray();
            $todasAsignaturas = \App\Models\Asignatura::whereNotIn('id', $asignaturasYaAsignadas)->get();

            $todosDocentes = \App\Models\Docente::with('usuario')->get();

            return view('academico.asignaciones.show', compact('aula', 'asignaciones', 'todosDocentes', 'todasAsignaturas'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('academico.asignaciones.index')->with('error', 'No pudimos cargar los detalles de asignación para esta aula.');
        }
    }


    // =========================================================================
    // 3. GESTOR DE HORARIOS
    // =========================================================================

    public function indexHorarios()
    {
        try {
            $this->authorize('viewAny', Aula::class); 
            $aulas = $this->getAulasPaginadas();

            // Apuntamos a la nueva vista exclusiva de horarios
            return view('academico.gestor-horarios.index', compact('aulas'));
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error inesperado al intentar abrir el Gestor de Horarios.');
        }
    }
}