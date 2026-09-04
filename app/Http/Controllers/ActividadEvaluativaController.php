<?php

namespace App\Http\Controllers;

use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\ActividadEvaluativa;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ActividadEvaluativaController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, \App\Models\AulaAsignaturaDocente $asignacion)
{
    // 1. Determinar si el usuario en sesión es supervisor (Directiva)
    /** @var \App\Models\Usuario $user */
    $user = auth()->user();
    $modoSupervision = $user->hasAnyRole(['Director', 'Subdirector']);

    // 2. Obtener los cortes evaluativos del año activo
    $cortes = \App\Models\CorteEvaluativo::whereHas('anioEscolar', fn($q) => $q->where('activo', true))->get();
    
    // IMPORTANTE: La vista manda el filtro como 'corte_id' en el select
    $corteSeleccionado = $request->query('corte_id', $cortes->first()->id ?? null);
    $corteActivo = $cortes->firstWhere('id', $corteSeleccionado);

    // 3. Traer las actividades correspondientes
    $actividades = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
        ->where('corte_evaluativo_id', $corteSeleccionado)
        ->get();

    // 4. Matemáticas para las barras de progreso
    $sumaAcumulados = $actividades->where('tipo', 'acumulado')->sum('puntaje_maximo');
    $sumaExamen = $actividades->where('tipo', 'examen')->sum('puntaje_maximo');

    return view('academico.notas.actividades', compact(
        'asignacion', 
        'modoSupervision', 
        'cortes', 
        'corteSeleccionado', 
        'corteActivo', 
        'actividades',
        'sumaAcumulados', 
        'sumaExamen'
    ));
}
    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'puntaje_maximo' => 'required|numeric|min:1',
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'tipo' => 'required|in:acumulado,examen',
            'descripcion' => 'nullable|string|max:500',
            'fecha' => 'nullable|date',
        ]);

        $corte = CorteEvaluativo::findOrFail($request->corte_evaluativo_id);

        // Sumamos solo las actividades del MISMO TIPO
        $sumaActual = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $corte->id)
            ->where('tipo', $request->tipo)
            ->sum('puntaje_maximo');

        $nuevoTotal = $sumaActual + $request->puntaje_maximo;
        
        // Asignamos el límite correcto
        $limite = $request->tipo === 'acumulado' ? $corte->peso_acumulado : $corte->peso_examen;
        
        if ($nuevoTotal > $limite) {
            return back()->with('error', 'Límite excedido. Te restan ' . ($limite - $sumaActual) . ' puntos disponibles en el ' . $request->tipo . '.');
        }

        ActividadEvaluativa::create([
            'aula_asignatura_docente_id' => $asignacion->id,
            'corte_evaluativo_id' => $request->corte_evaluativo_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'puntaje_maximo' => $request->puntaje_maximo,
            'tipo' => $request->tipo,
            'fecha' => $request->fecha ?? now(),
        ]);

        return back()->with('success', 'Actividad guardada correctamente.');
    }

    public function update(Request $request, AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        $this->authorize('calificar', $asignacion);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'puntaje_maximo' => 'required|numeric|min:1',
            'descripcion' => 'nullable|string|max:500',
            'fecha' => 'nullable|date',
        ]);

        $corte = CorteEvaluativo::findOrFail($actividad->corte_evaluativo_id);

        // Sumamos las demás actividades del MISMO tipo (excluyendo la actual)
        $sumaOtras = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $corte->id)
            ->where('tipo', $actividad->tipo)
            ->where('id', '!=', $actividad->id)
            ->sum('puntaje_maximo');

        $nuevoTotal = $sumaOtras + $request->puntaje_maximo;
        $limite = $actividad->tipo === 'acumulado' ? $corte->peso_acumulado : $corte->peso_examen;

        if ($nuevoTotal > $limite) {
            return back()->with('error', 'Límite excedido. Te restan ' . ($limite - $sumaOtras) . ' puntos disponibles en el ' . $actividad->tipo . '.');
        }

        $actividad->update([
            'nombre' => $request->nombre,
            'puntaje_maximo' => $request->puntaje_maximo,
            'descripcion' => $request->descripcion,
            'fecha' => $request->fecha,
        ]);

        return back()->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        $this->authorize('calificar', $asignacion);

        // Guardamos referencias antes de borrar para recalcular
        $corteId = $actividad->corte_evaluativo_id;
        $asignacionId = $asignacion->id;

        // Borramos las notas individuales de esta actividad
        \App\Models\NotaActividad::where('actividad_evaluativa_id', $actividad->id)->delete();
        $actividad->delete();

        // Recalculamos la nota final de cada matrícula del parcial
        $this->recalcularNotasParcial($asignacionId, $corteId);

        return back()->with('success', 'Actividad eliminada y notas recalculadas.');
    }

    /**
     * Recalcula la nota final (auto-suma) de un parcial para todas las matrículas.
     */
    protected function recalcularNotasParcial(int $asignacionId, int $corteId): void
    {
        $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->pluck('id');

        $matriculas = \App\Models\Matricula::where('aula_id', \App\Models\AulaAsignaturaDocente::find($asignacionId)->aula_id)
            ->where('estado', 'activo')
            ->pluck('id');

        foreach ($matriculas as $matriculaId) {
            $suma = \App\Models\NotaActividad::where('matricula_id', $matriculaId)
                ->whereIn('actividad_evaluativa_id', $actividades)
                ->sum('nota_obtenida');

            app(\App\Services\NotaService::class)->registrarNotaFinal($matriculaId, $asignacionId, $corteId, (float) $suma);
        }
    }
}