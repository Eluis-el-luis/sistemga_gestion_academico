<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AlumnoController extends Controller
{
    // Este Trait nos permite usar $this->authorize()
    use AuthorizesRequests; 

    /**
     * Muestra el listado de alumnos.
     */
    public function index()
    {
        // 1. Autorización: Llama al método viewAny de AlumnoPolicy
        $this->authorize('viewAny', Alumno::class);

        // 2. Consulta: Traemos a los alumnos ordenados alfabéticamente y paginados
        $alumnos = Alumno::orderBy('nombre_completo', 'asc')->paginate(15);

        // 3. Respuesta
        return view('academico.alumnos.index', compact('alumnos'));
    }

    /**
     * Muestra el formulario para crear un nuevo alumno.
     */
    public function create()
    {
        $this->authorize('create', Alumno::class);

        return view('academico.alumnos.create');
    }

    /**
     * Guarda el nuevo alumno en la base de datos.
     */
    public function store(StoreAlumnoRequest $request)
    {
        $this->authorize('create', Alumno::class);

        // El request ya viene validado gracias a StoreAlumnoRequest
        Alumno::create($request->validated());

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Alumno registrado exitosamente.');
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