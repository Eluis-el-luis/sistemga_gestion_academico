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

        // 🌟 CORRECCIÓN 1: Separamos 'aula.grado' y 'aula.modalidad'
        $query = Matricula::with(['alumno', 'aula.grado', 'aula.modalidad', 'anioEscolar']);

        // --- FILTROS ---
        if ($request->filled('anio_escolar_id')) {
            $query->where('anio_escolar_id', $request->anio_escolar_id);
        } elseif (!$request->has('anio_escolar_id') && $anioActivo) {
            $query->where('anio_escolar_id', $anioActivo->id);
        }

        if ($request->filled('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $matriculas = $query->latest('fecha_matricula')->paginate(15);

        // 🌟 CORRECCIÓN 2: Separamos en un arreglo ['grado', 'modalidad']
        $aniosEscolares = AnioEscolar::all();
        $aulas = Aula::with(['grado', 'modalidad'])->get();

        return view('academico.matriculas.index', compact('matriculas', 'aniosEscolares', 'aulas', 'anioActivo'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Matricula::class);

        $alumnos = Alumno::orderBy('nombre_completo')->get();
        // 🌟 CORRECCIÓN 3: Aseguramos cargar ambas relaciones aquí también
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
    
    // Tus métodos retirar, reactivar y destroy van aquí abajo...
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