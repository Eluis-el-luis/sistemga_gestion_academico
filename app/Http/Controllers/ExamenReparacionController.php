<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
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
     * Listado de exámenes de reparación y alumnos con asignaturas aplazadas.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExamenReparacion::class);

        $usuario = auth()->user();

        $aulas = Aula::with(['grado', 'anioEscolar'])
            ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
            ->when($usuario->docente && !$usuario->hasRole(['Director', 'Subdirector']), function ($q) use ($usuario) {
                $q->where('docente_guia_id', $usuario->docente->id);
            })
            ->get();

        $aulaSeleccionada = $request->query('aula_id', $aulas->first()->id ?? null);

        $matriculas = collect();
        $asignaturas = collect();

        if ($aulaSeleccionada) {
            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aulaSeleccionada)
                ->where('estado', 'activo')
                ->get();

            // Asignaturas impartidas en esta aula (vía asignaciones)
            $asignaturas = Asignatura::whereIn('id',
                AulaAsignaturaDocente::where('aula_id', $aulaSeleccionada)->pluck('asignatura_id')
            )->get();
        }

        return view('academico.reparacion.index', compact(
            'aulas', 'aulaSeleccionada', 'matriculas', 'asignaturas'
        ));
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