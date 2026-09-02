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

    public function index(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $cortes = CorteEvaluativo::whereHas('anioEscolar', fn($q) => $q->where('activo', true))->get();
        $corteSeleccionado = $request->query('corte_evaluativo_id', $cortes->first()->id ?? null);
        $corte = $cortes->firstWhere('id', $corteSeleccionado);

        $actividades = ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $corteSeleccionado)
            ->get();

        // Matemáticas para las barras de progreso
        $sumaAcumulado = $actividades->where('tipo', 'acumulado')->sum('puntaje_maximo');
        $porcentajeAcumulado = ($corte && $corte->peso_acumulado > 0) ? ($sumaAcumulado / $corte->peso_acumulado) * 100 : 0;

        $sumaExamen = $actividades->where('tipo', 'examen')->sum('puntaje_maximo');
        $porcentajeExamen = ($corte && $corte->peso_examen > 0) ? ($sumaExamen / $corte->peso_examen) * 100 : 0;

        return view('academico.notas.index', compact(
            'asignacion', 'cortes', 'corteSeleccionado', 'corte', 'actividades',
            'sumaAcumulado', 'porcentajeAcumulado', 'sumaExamen', 'porcentajeExamen'
        ));
    }

    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'puntaje_maximo' => 'required|numeric|min:1',
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'tipo' => 'required|in:acumulado,examen'
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

    public function destroy(AulaAsignaturaDocente $asignacion, ActividadEvaluativa $actividad)
    {
        $this->authorize('calificar', $asignacion);
        
        // Usamos la tabla correcta que creamos en las migraciones para evitar errores al borrar
        DB::table('nota_actividad')->where('actividad_evaluativa_id', $actividad->id)->delete();
        $actividad->delete();
        
        return back()->with('success', 'Actividad eliminada.');
    }
}