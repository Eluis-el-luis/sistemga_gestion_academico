<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Http\Requests\StoreMatriculaRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MatriculaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Matricula::class);

        $anioActivo = AnioEscolar::where('activo', true)->first();
        $query = Matricula::with(['alumno', 'aula.grado', 'aula.modalidad', 'anioEscolar']);

        // --- FILTRO DE AÑO ---
        if ($request->filled('anio_escolar_id')) {
            $query->where('anio_escolar_id', $request->anio_escolar_id);
        } elseif (!$request->has('anio_escolar_id') && $anioActivo) {
            $query->where('anio_escolar_id', $anioActivo->id);
        }

        // --- BÚSQUEDA INTELIGENTE (ILIKE para PostgreSQL) ---
        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->whereHas('alumno', function($q) use ($busqueda) {
                $q->where('nombre_completo', 'ilike', "%{$busqueda}%")
                  ->orWhere('codigo_unico_persona', 'ilike', "%{$busqueda}%");
            });
        }

        if ($request->filled('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $matriculas = $query->latest('fecha_matricula')->paginate(15);

        $aniosEscolares = AnioEscolar::orderBy('nombre', 'desc')->get();
        $aulas = Aula::with(['grado', 'modalidad'])->get();

        return view('academico.matriculas.index', compact('matriculas', 'aniosEscolares', 'aulas', 'anioActivo'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Matricula::class);

        $anioActivo = AnioEscolar::where('activo', true)->first();
        
        // 🔒 BLOQUEO DE DOBLE MATRÍCULA: 
        // Solo traemos alumnos que NO tengan matrícula en el año activo
        $alumnos = Alumno::whereDoesntHave('matriculas', function($q) use ($anioActivo) {
            if ($anioActivo) {
                $q->where('anio_escolar_id', $anioActivo->id);
            }
        })->orderBy('nombre_completo')->get();

        $aulas = Aula::with(['grado', 'modalidad'])->get();
        $anios = AnioEscolar::where('activo', true)->get();
        
        $alumnoSeleccionado = $request->query('alumno_id');

        return view('academico.matriculas.create', compact('alumnos', 'aulas', 'anios', 'alumnoSeleccionado'));
    }

    public function store(StoreMatriculaRequest $request)
    {
        $this->authorize('create', Matricula::class);
        Matricula::create($request->validated());

        return redirect()->route('academico.matriculas.index')
                         ->with('success', 'Matrícula procesada exitosamente.');
    }
    
    public function retirar(Request $request, Matricula $matricula)
    {
        $this->authorize('update', $matricula);
        $matricula->update(['estado' => 'retirado']);
        return redirect()->route('academico.matriculas.index')->with('success', 'Estudiante retirado correctamente.');
    }

    public function reactivar(Request $request, Matricula $matricula)
    {
        $this->authorize('update', $matricula);
        $matricula->update(['estado' => 'activo']);
        return redirect()->route('academico.matriculas.index')->with('success', 'Matrícula reactivada correctamente.');
    }

    public function destroy(Request $request, Matricula $matricula)
    {
        $this->authorize('delete', $matricula);
        $matricula->delete();
        return redirect()->route('academico.matriculas.index')->with('success', 'Matrícula eliminada (borrado lógico).');
    }
}