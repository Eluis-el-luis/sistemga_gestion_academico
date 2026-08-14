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

        // Obtenemos al alumno mediante el ID enviado por la URL
        $alumno = Alumno::findOrFail($request->alumno_id);
        
        // Cargamos los catálogos necesarios para los "select" del formulario
        $aulas = Aula::with('grado')->get();
        $anios = AnioEscolar::where('activo', true)->get(); // Traemos solo los años activos

        return view('academico.matriculas.create', compact('alumno', 'aulas', 'anios'));
    }

    /**
     * Guarda la matrícula en la base de datos.
     */
    public function store(StoreMatriculaRequest $request)
    {
        $this->authorize('create', Matricula::class);

        Matricula::create($request->validated());

        // Redirigimos de vuelta a la ficha del alumno
        return redirect()->route('academico.alumnos.show', $request->alumno_id)
                         ->with('success', 'Alumno matriculado exitosamente para este periodo.');
    }
}