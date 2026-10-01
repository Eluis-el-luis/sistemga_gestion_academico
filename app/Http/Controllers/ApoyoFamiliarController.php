<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\CorteEvaluativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApoyoFamiliarController extends Controller
{
    public function index()
    {
        try {
            $usuario = auth()->user();
            
            // 1. Identificar el aula a cargo del Maestro Guía
            $aula = Aula::with('grado')
                ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
                ->where('docente_guia_id', $usuario->docente->id ?? 0)
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como docente guía para este año escolar.');
            }

            // 2. Determinar el corte evaluativo activo
            $hoy = now()->timezone('America/Managua')->toDateString();
            $corteActivo = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
                ->where('fecha_inicio', '<=', $hoy)
                ->where('fecha_fin', '>=', $hoy)
                ->first() ?? CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderBy('numero', 'desc')->first();

            $matriculas = Matricula::with(['alumno', 'evaluacionesFamiliares' => function($q) use ($corteActivo) {
                if ($corteActivo) {
                    $q->where('corte_evaluativo_id', $corteActivo->id);
                }
            }])
            ->where('aula_id', $aula->id)
            ->where('estado', 'activo')
            ->orderBy('id')
            ->get();

            // 4. Calcular el Termómetro del Aula convirtiendo letras a números
            $totalEvaluados = 0;
            $sumaPuntos = 0;

            foreach ($matriculas as $matricula) {
                $evaluacion = $matricula->evaluacionesFamiliares->first()->evaluacion ?? null;
                
                if ($evaluacion) {
                    $totalEvaluados++;
                    // Conversión interna estricta a valores numéricos representativos
                    if ($evaluacion === 'EX') $sumaPuntos += 95;
                    elseif ($evaluacion === 'MB') $sumaPuntos += 85;
                    elseif ($evaluacion === 'B') $sumaPuntos += 60;
                }
            }

            $promedioAula = $totalEvaluados > 0 ? round($sumaPuntos / $totalEvaluados) : 0;

            return view('academico.apoyo_familiar.index', compact('aula', 'matriculas', 'corteActivo', 'promedioAula', 'totalEvaluados'));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Protege contra errores de cálculo, fechas o relaciones de BD rotas
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar las evaluaciones de Apoyo Familiar. Por favor, inténtalo de nuevo.');
        }
    }

    public function store(Request $request)
    {
        // Se valida fuera del try para mantener los mensajes de error del formulario vivos
        $request->validate([
            'corte_evaluativo_id' => 'required|exists:corte_evaluativo,id',
            'evaluaciones'        => 'required|array',
            'evaluaciones.*'      => 'nullable|in:B,MB,EX',
        ]);

        $corteId = $request->corte_evaluativo_id;

        DB::beginTransaction();
        try {
            foreach ($request->evaluaciones as $matriculaId => $valor) {
                if ($valor) {
                    // Inserta o actualiza la evaluación cualitativa en la base de datos
                    DB::table('evaluacion_familiar')->updateOrInsert(
                        [
                            'matricula_id' => $matriculaId,
                            'corte_evaluativo_id' => $corteId
                        ],
                        [
                            'evaluacion' => $valor,
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );
                }
            }
            DB::commit();
            
            return back()->with('success', 'Evaluaciones guardadas.'); 

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Revierte la BD a su estado anterior si un updateOrInsert falla
            DB::rollBack();
            return back()->with('error', 'Ocurrió un problema al intentar guardar las evaluaciones en la base de datos.');
        }
    }
}