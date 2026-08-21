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

        $query = Alumno::with(['matriculas' => function($q) {
            $q->latest('fecha_matricula');
        }, 'matriculas.aula.grado', 'matriculas.aula.modalidad']);

        // --- 1. ALCANCE POR ROL (Scope) ---
        $usuario = auth()->user();
        if ($usuario->hasRole('Docente Guía')) {
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
            if ($docente) {
                $aulas_id = \App\Models\Aula::where('docente_guia_id', $docente->id)->pluck('id');
                $query->whereHas('matriculas', function($q) use ($aulas_id) {
                    $q->whereIn('aula_id', $aulas_id)->where('estado', 'activo');
                });
            }
        }

        // --- 2. FILTROS EN PANTALLA (Corregidos) ---
        if ($request->filled('modalidad_id')) {
            $query->whereHas('matriculas', function($q) use ($request) {
                $q->whereHas('aula', function($q2) use ($request) {
                    $q2->where('modalidad_id', $request->modalidad_id);
                });
            });
        }

        if ($request->filled('grado_id')) {
            $query->whereHas('matriculas', function($q) use ($request) {
                $q->whereHas('aula', function($q2) use ($request) {
                    $q2->where('grado_id', $request->grado_id);
                });
            });
        }

        if ($request->filled('aula_id')) {
            $query->whereHas('matriculas', function($q) use ($request) {
                $q->where('aula_id', $request->aula_id);
            });
        }

        if ($request->filled('estado')) {
            $query->whereHas('matriculas', function($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        }

        $alumnos = $query->paginate(15);

        $modalidades = \App\Models\Modalidad::all();
        $grados = \App\Models\Grado::all();
        $aulas = \App\Models\Aula::all();

        return view('academico.alumnos.index', compact('alumnos', 'modalidades', 'grados', 'aulas'));
    }

    public function create()
    {
        $this->authorize('create', Alumno::class);
        return view('academico.alumnos.create');
    }

    public function store(StoreAlumnoRequest $request)
    {
        $this->authorize('create', Alumno::class);
        Alumno::create($request->validated());

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Expediente del alumno registrado con éxito. El Docente Guía ya puede proceder con su matrícula.');
    }

    public function show(Alumno $alumno)
    {
        $this->authorize('viewAny', Alumno::class);
        $alumno->load(['matriculas.aula.grado', 'matriculas.anioEscolar']);
        return view('academico.alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        return view('academico.alumnos.edit', compact('alumno'));
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        $alumno->update($request->validated());

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Datos del alumno actualizados correctamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $this->authorize('update', $alumno);
        $alumno->delete();

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Alumno eliminado del sistema.');
    }
}