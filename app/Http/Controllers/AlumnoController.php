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

        // Preparamos la consulta trayendo las matrículas para mostrar el grado en la tabla
        $query = Alumno::with(['matriculas' => function($q) {
            $q->latest('fecha_matricula');
        }, 'matriculas.aula.grado', 'matriculas.aula.modalidad']);

        // --- 1. ALCANCE POR ROL (Scope para Docentes Guías) ---
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

        if ($request->filled('estado')) {
            $query->whereHas('matriculas', function($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        }

        // --- 4. ORDEN ALFABÉTICO Y PAGINACIÓN ---
        $alumnos = $query->orderBy('nombre_completo', 'asc')
                         ->paginate(15)
                         ->withQueryString(); // <- Mantiene los filtros activos al cambiar de página

        // Traemos los catálogos para llenar los <select> de los filtros
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
                         ->with('success', 'Expediente del alumno registrado con éxito. El Docente Guía ya puede proceder con su matrícula en el aula correspondiente.');
    }

    public function show(Alumno $alumno)
    {
        $this->authorize('viewAny', Alumno::class);
        
        // Eager Loading: Cargamos las matrículas, el aula, el grado y el año escolar
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
        $this->authorize('delete', $alumno);
        
        $alumno->delete();

        return redirect()->route('academico.alumnos.index')
                         ->with('success', 'Alumno eliminado del sistema.');
    }
}