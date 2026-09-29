<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    use AuthorizesRequests; 

    public function index(Request $request)
    {
        $this->authorize('viewAny', Alumno::class);

        try {
            // Preparamos la consulta trayendo las matrículas para mostrar el grado en la tabla
            $query = Alumno::with(['matriculas' => function($q) {
                $q->latest('fecha_matricula');
            }, 'matriculas.aula.grado', 'matriculas.aula.modalidad']);

            // --- 1. ALCANCE POR ROL (Scope para Docentes Guías) ---
            $usuario = auth()->user();
            if ($usuario->hasRole('Docente Guia')) {
                $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
                if ($docente) {
                    $aulas_id = \App\Models\Aula::where('docente_guia_id', $docente->id)->pluck('id');
                    $query->whereHas('matriculas', function($q) use ($aulas_id) {
                        $q->whereIn('aula_id', $aulas_id)->where('estado', 'activo');
                    });
                }
            }

            // --- 2. MOTOR DE BÚSQUEDA (Nombre o CUP) ---
            if ($request->filled('buscar')) {
                $busqueda = $request->buscar;
                $query->where(function($q) use ($busqueda) {
                    $q->where('nombre_completo', 'like', "%{$busqueda}%")
                      ->orWhere('codigo_unico_persona', 'like', "%{$busqueda}%");
                });
            }

            // --- 3. FILTROS EN PANTALLA (Cascada) ---
            if ($request->filled('modalidad_id')) {
                $query->whereHas('matriculas.aula', function($q) use ($request) {
                    $q->where('modalidad_id', $request->modalidad_id);
                });
            }

            if ($request->filled('grado_id')) {
                $query->whereHas('matriculas.aula', function($q) use ($request) {
                    $q->where('grado_id', $request->grado_id);
                });
            }

            if ($request->filled('aula_id')) {
                $query->whereHas('matriculas', function($q) use ($request) {
                    $q->where('aula_id', $request->aula_id);
                });
            }

            // Seleccionado "activo" por defecto si no hay filtros aplicados
            $estadoFiltro = $request->get('estado', !request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']) ? 'activo' : null);

            if (!empty($estadoFiltro)) {
                $query->whereHas('matriculas', function($q) use ($estadoFiltro) {
                    $q->where('estado', $estadoFiltro);
                });
            }

            // --- 4. ORDEN ALFABÉTICO Y PAGINACIÓN ---
            $alumnos = $query->orderBy('nombre_completo', 'asc')
                             ->paginate(15)
                             ->withQueryString(); // Mantiene los filtros activos al cambiar de página

            // Traemos los catálogos para llenar los <select> de los filtros
            $modalidades = \App\Models\Modalidad::all();
            $grados = \App\Models\Grado::all();
            $aulas = \App\Models\Aula::all();

            return view('academico.alumnos.index', compact('alumnos', 'modalidades', 'grados', 'aulas'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la consulta o carga de catálogos
            return redirect()->route('dashboard')->with('error', 'Lo sentimos, no pudimos cargar el listado de alumnos. Por favor, intenta nuevamente.');
        }
    }

    public function create()
    {
        $this->authorize('create', Alumno::class);
        
        try {
            return view('academico.alumnos.create');
        } catch (\Exception $e) {
            return redirect()->route('academico.alumnos.index')->with('error', 'No se pudo abrir el formulario de registro.');
        }
    }

    public function store(StoreAlumnoRequest $request)
    {
        $this->authorize('create', Alumno::class);
        
        // Extraemos los datos validados fuera del try-catch
        $datosValidados = $request->validated();

        try {
            Alumno::create($datosValidados);

            return redirect()->route('academico.alumnos.index')
                             ->with('success', 'Expediente del alumno registrado con éxito. El Docente Guía ya puede proceder con su matrícula en el aula correspondiente.');
                             
        } catch (\Exception $e) {
            // CONTINGENCIA: Falla al guardar en la base de datos (ej. CUP duplicado en BD que no atrapó el validador)
            return back()->withInput()->with('error', 'Ocurrió un error inesperado al intentar guardar el expediente. Revisa los datos e intenta nuevamente.');
        }
    }

    public function show(Alumno $alumno)
    {
        $this->authorize('viewAny', Alumno::class);
        
        try {
            $alumno->load(['matriculas.aula.grado', 'matriculas.anioEscolar']);
            
            return view('academico.alumnos.show', compact('alumno'));
            
        } catch (\Exception $e) {
            return redirect()->route('academico.alumnos.index')->with('error', 'No pudimos cargar los detalles de este alumno. Es posible que los datos estén corruptos.');
        }
    }

    public function edit(Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        
        try {
            return view('academico.alumnos.edit', compact('alumno'));
        } catch (\Exception $e) {
            return redirect()->route('academico.alumnos.index')->with('error', 'No se pudo abrir el formulario de edición para este alumno.');
        }
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        
        $datosValidados = $request->validated();

        try {
            $alumno->update($datosValidados);

            return redirect()->route('academico.alumnos.index')
                             ->with('success', 'Datos del alumno actualizados correctamente.');
                             
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Hubo un problema al actualizar los datos del alumno en la base de datos.');
        }
    }

    public function destroy(Alumno $alumno)
    {
        $this->authorize('delete', $alumno);
        
        try {
            $alumno->delete();

            return redirect()->route('academico.alumnos.index')
                             ->with('success', 'Alumno eliminado del sistema.');
                             
        } catch (\Exception $e) {
            // CONTINGENCIA: Muy útil aquí por si el alumno ya tiene matrículas, notas, etc. y la BD bloquea el borrado
            return back()->with('error', 'No se puede eliminar este alumno. Es probable que tenga matrículas o calificaciones asociadas que lo protegen.');
        }
    }

    public function misAlumnos()
    {
        try {
            /** @var \App\Models\Usuario $usuario */
            $usuario = auth()->user();
            
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->firstOrFail();
            
            // Obtener el aula donde este docente es el guía principal
            $aula = \App\Models\Aula::with('grado')->where('docente_guia_id', $docente->id)->first();
            
            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como Maestro Guía.');
            }

            // Traer únicamente a los alumnos matriculados en esta aula
            $alumnos = \App\Models\Alumno::whereHas('matriculas', function($q) use ($aula) {
                $q->where('aula_id', $aula->id)->where('estado', 'activo');
            })->get();

            // NOTA TÉCNICA: Aquí deberás iterar sobre $alumnos para sumar sus promedios 
            // y contar sus clases reprobadas según tu tabla de notas. 
            // Para renderizar la vista y avanzar, enviaremos la estructura base.

            return view('academico.alumnos.mis-alumnos', compact('aula', 'alumnos'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si el usuario no tiene perfil de docente u ocurre otro error
            return redirect()->route('dashboard')->with('error', 'Hubo un problema al intentar cargar tu lista de alumnos.');
        }
    }
}