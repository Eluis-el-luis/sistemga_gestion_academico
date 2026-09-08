<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Asignatura;
use App\Models\ExamenReparacion;
use App\Models\Matricula;
use App\Services\ReparacionService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamenReparacionController extends Controller
{
    use AuthorizesRequests;

    protected $reparacionService;

    public function __construct(ReparacionService $reparacionService)
    {
        $this->reparacionService = $reparacionService;
    }

    /**
     * Listado de exámenes de reparación: alumnos con asignaturas reprobadas
     * (excluye grados de promoción automática).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExamenReparacion::class);

        $usuario = auth()->user();
        $anioActivo = AnioEscolar::where('activo', true)->first();

        // Grados con promoción automática (no requieren examen de reparación)
        $gradosAutoPromovidos = [1, 2, 3, 4, 5];

        $matriculasRaw = Matricula::with(['alumno', 'aula.grado'])
            ->when($anioActivo, fn ($q) => $q->where('anio_escolar_id', $anioActivo->id))
            ->where('estado', 'activo')
            ->whereHas('aula', function ($q) use ($gradosAutoPromovidos) {
                $q->whereNotIn('grado_id', $gradosAutoPromovidos);
            })
            ->when($usuario->docente && !$usuario->hasRole(['Director', 'Subdirector']), function ($q) use ($usuario) {
                $q->whereHas('aula', fn ($q2) => $q2->where('docente_guia_id', $usuario->docente->id));
            })
            ->get();

        $matriculas = $matriculasRaw->map(function ($matricula) {
            // Notas reprobadas (nota anual < 60) con su asignatura
            $matricula->clases_reprobadas = \App\Models\Nota::with('aulaAsignaturaDocente.asignatura')
                ->where('matricula_id', $matricula->id)
                ->whereNotNull('nota_cuantitativa')
                ->where('nota_cuantitativa', '<', 60)
                ->get();

            return $matricula;
        })
        ->filter(function ($matricula) {
            return $matricula->clases_reprobadas->count() > 0;
        })
        ->values();

        return view('academico.reparacion.index', compact('matriculas'));
    }

    /**
     * Registra el examen de reparación de un alumno en una asignatura.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ExamenReparacion::class);

        $request->validate([
            'matricula_id' => 'required|exists:matricula,id',
            'asignatura_id' => 'required|exists:asignatura,id',
            'nota_obtenida' => 'required|numeric|min:0|max:100',
            'fecha' => 'required|date',
        ]);

        $matricula = Matricula::findOrFail($request->matricula_id);
        $asignatura = Asignatura::findOrFail($request->asignatura_id);

        $this->reparacionService->registrar(
            $matricula,
            $asignatura,
            (float) $request->nota_obtenida,
            $request->fecha
        );

        return back()->with('success', 'Examen de reparación registrado correctamente.');
    }

    /**
     * Elimina un registro de examen de reparación.
     */
    public function destroy(ExamenReparacion $examen)
    {
        $this->authorize('delete', $examen);
        $examen->delete();

        return back()->with('success', 'Examen de reparación eliminado.');
    }
}