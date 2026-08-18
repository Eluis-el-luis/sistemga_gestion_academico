<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;


class AlumnoController extends Controller
{
    // Este Trait nos permite usar $this->authorize()
    use AuthorizesRequests; 

    /**
     * Muestra el listado de alumnos.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Alumno::class);

        // Preparamos la consulta trayendo también la última matrícula del alumno
        $query = Alumno::with(['matriculas' => function($q) {
            $q->latest('fecha_matricula');
        }, 'matriculas.aula.grado', 'matriculas.aula.modalidad']);

        // --- 1. ALCANCE POR ROL (Scope) ---
        $usuario = auth()->user();
        if ($usuario->hasRole('Docente Guía')) {
            // Buscamos si el usuario está asociado a un docente, y sacamos sus aulas
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
            if ($docente) {
                $aulas_id = \App\Models\Aula::where('docente_guia_id', $docente->id)->pluck('id');
                // Filtramos para que solo vea a los alumnos matriculados en SUS aulas
                $query->whereHas('matriculas', function($q) use ($aulas_id) {
                    $q->whereIn('aula_id', $aulas_id)->where('estado', 'activo');
                });
            }
        }

        // --- 2. FILTROS EN PANTALLA ---
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

        $alumnos = $query->paginate(15);

        // Traemos los catálogos para llenar los <select> de los filtros
        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        $aulas = \App\Models\Aula::all();

        return view('academico.alumnos.index', compact('alumnos', 'modalidades', 'grados', 'aulas'));
    }

    /**
     * Muestra el formulario para crear un nuevo alumno.
     */
    public function create()
    {
        $this->authorize('create', Alumno::class);

        return view('academico.alumnos.create');
    }

    public function store(StoreAlumnoRequest $request)
    {
        $this->authorize('create', Alumno::class);

        Alumno::create($request->validated());

        // Redirigimos al directorio de alumnos en vez de la matrícula
        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Expediente del alumno registrado con éxito. El Docente Guía ya puede proceder con su matrícula en el aula correspondiente.');
    }

    /**
     * Muestra la ficha completa de un alumno (y su historial de matrículas).
     */
    public function show(Alumno $alumno)
    {
        $this->authorize('viewAny', Alumno::class);

        // Eager Loading: Cargamos las matrículas, el aula, el grado y el año escolar
        // Esto cumple con el requerimiento de "ficha de alumno con su historial"
        $alumno->load(['matriculas.aula.grado', 'matriculas.anioEscolar']);

        return view('academico.alumnos.show', compact('alumno'));
    }

    /**
     * Muestra el formulario para editar un alumno.
     */
    public function edit(Alumno $alumno)
    {
        $this->authorize('update', $alumno);

        return view('academico.alumnos.edit', compact('alumno'));
    }

    /**
     * Actualiza los datos del alumno.
     */
    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $this->authorize('update', $alumno);

        $alumno->update($request->validated());

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Datos del alumno actualizados correctamente.');
    }

    /**
     * Elimina el registro del alumno (si aplica).
     */
    public function destroy(Alumno $alumno)
    {
        // En la Matriz, la acción de Gestionar (G) abarca crear, editar y eliminar.
        $this->authorize('update', $alumno);

        $alumno->delete();

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Alumno eliminado del sistema.');
    }
}