<?php

namespace App\Http\Controllers;

use App\Models\AvanceContenido;
use App\Models\AulaAsignaturaDocente;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AvanceContenidoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Pantalla principal de seguimiento de contenidos: muestra las asignaturas
     * del docente y su avance por mes.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AvanceContenido::class);

        $usuario = auth()->user();

        if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador'])) {
            $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente'])->get();
            $modoSupervision = true;
        } else {
            $docente = $usuario->docente;
            $asignaciones = $docente
                ? AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->where('docente_id', $docente->id)->get()
                : collect();
            $modoSupervision = false;
        }

        $asignacionSeleccionada = null;
        $avances = collect();

        $asignacionId = $request->query('asignacion_id', $asignaciones->first()->id ?? null);

        if ($asignacionId) {
            $asignacionSeleccionada = AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->find($asignacionId);
            if ($asignacionSeleccionada) {
                $avances = AvanceContenido::where('aula_asignatura_docente_id', $asignacionId)
                    ->orderBy('mes')
                    ->get();
            }
        }

        return view('academico.avance.index', compact(
            'asignaciones', 'modoSupervision', 'asignacionSeleccionada', 'avances'
        ));
    }

    /**
     * Registra o actualiza el avance de contenidos de una asignación en un mes específico.
     */
    public function store(Request $request)
    {
        $this->authorize('create', AvanceContenido::class);

        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'mes' => 'required|date_format:Y-m',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
        ]);

        // Autorización de alcance: solo el docente dueño de la asignación (o supervisor)
        $asignacion = AulaAsignaturaDocente::findOrFail($request->aula_asignatura_docente_id);

        $docente = auth()->user()->docente;
        if ($docente && $asignacion->docente_id !== $docente->id
            && !auth()->user()->hasRole(['Director', 'Subdirector'])) {
            abort(403, 'No puede registrar avance de una asignación que no imparte.');
        }

        AvanceContenido::updateOrCreate(
            [
                'aula_asignatura_docente_id' => $asignacion->id,
                'mes' => $request->mes,
            ],
            [
                'porcentaje_avance' => $request->porcentaje_avance,
            ]
        );

        return back()->with('success', 'Avance de contenidos registrado correctamente.');
    }

    /**
     * Elimina un registro de avance.
     */
    public function destroy(AvanceContenido $avance)
    {
        $this->authorize('delete', $avance);
        $avance->delete();

        return back()->with('success', 'Registro de avance eliminado.');
    }
}