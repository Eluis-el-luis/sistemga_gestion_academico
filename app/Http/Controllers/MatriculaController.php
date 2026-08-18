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

    /**
     * Muestra el formulario para matricular a un alumno específico.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Matricula::class);

        $alumnos = Alumno::orderBy('nombre_completo')->get();
        $aulas = Aula::with('grado')->get();
        $anios = AnioEscolar::where('activo', true)->get();
        
        // Capturamos el ID si venimos del flujo de "Nuevo Ingreso"
        $alumnoSeleccionado = $request->query('alumno_id');

        return view('academico.matriculas.create', compact('alumnos', 'aulas', 'anios', 'alumnoSeleccionado'));
    }

    /**
     * Guarda la matrícula en la base de datos.
     */
    public function store(StoreMatriculaRequest $request)
    {
        $this->authorize('create', Matricula::class);

        Matricula::create($request->validated());

        // Redirigimos al índice general de matrículas
        return redirect()->route('academico.matriculas.index')
                         ->with('success', 'Matrícula procesada exitosamente.');
    }

    /**
     * Muestra el listado general de matrículas.
     */
    public function index()
    {
        $this->authorize('viewAny', Matricula::class);

        // Traemos las matrículas con sus relaciones para no saturar la base de datos (Eager Loading)
        $matriculas = Matricula::with(['alumno', 'aula.grado', 'anioEscolar'])
                        ->latest('fecha_matricula')
                        ->paginate(15);

        return view('academico.matriculas.index', compact('matriculas'));
    }
}