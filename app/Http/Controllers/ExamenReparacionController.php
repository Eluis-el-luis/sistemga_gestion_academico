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
        try {
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

        } catch (\Exception $e) {
            // CONTINGENCIA: Si las iteraciones fallan por datos inconsistentes en la base de datos
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar el listado de alumnos en reparación. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Registra el examen de reparación de un alumno en una asignatura.
     */
    public function store(Request $request)
    {
        // Validación AFUERA del try-catch para mantener activos los mensajes del formulario
        $request->validate([
            'matricula_id' => 'required|exists:matricula,id',
            'asignatura_id' => 'required|exists:asignatura,id',
            'nota_obtenida' => 'required|numeric|min:0|max:100',
            'fecha' => 'required|date',
        ]);

        try {
            $this->authorize('create', ExamenReparacion::class);

            $matricula = Matricula::findOrFail($request->matricula_id);
            $asignatura = Asignatura::findOrFail($request->asignatura_id);

            $this->reparacionService->registrar(
                $matricula,
                $asignatura,
                (float) $request->nota_obtenida,
                $request->fecha
            );

            return back()->with('success', 'Examen de reparación registrado correctamente.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si el ReparacionService falla o la BD rechaza los datos
            return back()->withInput()->with('error', 'Hubo un error técnico al registrar la nota de reparación. Verifica los datos e intenta nuevamente.');
        }
    }

    /**
     * Elimina un registro de examen de reparación.
     */
    public function destroy(ExamenReparacion $examen)
    {
        try {
            $this->authorize('delete', $examen);
            $examen->delete();

            return back()->with('success', 'Examen de reparación eliminado.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si la BD bloquea la eliminación
            return back()->with('error', 'No se pudo eliminar el examen de reparación. Es posible que existan dependencias protegidas.');
        }
    }
}