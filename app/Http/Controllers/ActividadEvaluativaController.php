<?php

namespace App\Http\Controllers;

use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\ActividadEvaluativa;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActividadEvaluativaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);

        // Cargamos la asignatura, el aula, y vitalmente: el docente y su usuario
        $asignacion->load('aula.grado', 'asignatura', 'docente.usuario');

        // Determinamos si quien entra es un supervisor (Solo Lectura) o el docente (Escritura)
        $usuario = auth()->user();
        $modoSupervision = $usuario->hasRole(['Subdirector', 'Director', 'Coordinador', 'Gestor de Usuarios']);

        $cortes = CorteEvaluativo::whereHas('anioEscolar', function($q) {
            $q->where('activo', true);
        })->orderBy('numero')->get();

        $corteSeleccionado = $request->query('corte_id', $cortes->first()->id ?? null);
        $corteActivo = $cortes->where('id', $corteSeleccionado)->first();

        $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $corteSeleccionado)
            ->get();

        $sumaAcumulados = $actividades->where('tipo', 'acumulado')->sum('puntaje_maximo');
        $sumaExamen = $actividades->where('tipo', 'examen')->sum('puntaje_maximo');

        return view('academico.notas.actividades', compact(
            'asignacion', 'cortes', 'corteSeleccionado', 'corteActivo', 
            'actividades', 'sumaAcumulados', 'sumaExamen', 'modoSupervision'
        ));
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);

        $request->validate([
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:acumulado,examen', 
            'puntaje_maximo' => 'required|integer|min:1|max:100'
        ]);

        $corte = CorteEvaluativo::findOrFail($request->corte_evaluativo_id);

        $sumaActual = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $corte->id)
            ->where('tipo', $request->tipo)
            ->sum('puntaje_maximo');

        $limite = $request->tipo === 'acumulado' ? $corte->peso_acumulado : $corte->peso_examen;

        if (($sumaActual + $request->puntaje_maximo) > $limite) {
            return back()->with('error', "¡Límite superado! La Subdirección fijó {$limite} pts máximo para {$request->tipo}. Ya llevas {$sumaActual} pts asignados.");
        }

        ActividadEvaluativa::create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $corte->id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'puntaje_maximo' => $request->puntaje_maximo
        ]);

        return back()->with('success', 'Actividad evaluativa registrada correctamente.');
    }

    public function destroy(AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        $this->authorize('calificar', $asignacion);
        
        \App\Models\CalificacionActividad::where('actividad_evaluativa_id', $actividad->id)->delete();
        $actividad->delete();
        
        return back()->with('success', 'Actividad eliminada.');
    }
}