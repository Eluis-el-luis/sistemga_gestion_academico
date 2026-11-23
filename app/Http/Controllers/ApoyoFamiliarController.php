<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\CorteEvaluativo;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApoyoFamiliarController extends Controller
{
    public function index()
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();

            $aula = Aula::with('grado')
                ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                ->where('docente_guia_id', $usuario->docente->id ?? 0)
                ->first();

            if (!$aula) {
                return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como docente guía para este año escolar.');
            }

            $hoy = now()->timezone('America/Managua')->toDateString();
            $corteActivo = CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)
                ->where('fecha_inicio', '<=', $hoy)
                ->where('fecha_fin', '>=', $hoy)
                ->first() ?? CorteEvaluativo::where('anio_escolar_id', $aula->anio_escolar_id)->orderByDesc('numero')->first();

            $matriculas = Matricula::with(['alumno', 'evaluacionesFamiliares' => function ($q) use ($corteActivo) {
                if ($corteActivo) {
                    $q->where('corte_evaluativo_id', $corteActivo->id);
                }
            }])
                ->where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->orderBy('id')
                ->get();

            $totalEvaluados = 0;
            $sumaPuntos = 0;

            foreach ($matriculas as $matricula) {
                $evaluacion = $matricula->evaluacionesFamiliares->first()->evaluacion ?? null;
                if ($evaluacion) {
                    $totalEvaluados++;
                    if ($evaluacion === 'EX') {
                        $sumaPuntos += 95;
                    } elseif ($evaluacion === 'MB') {
                        $sumaPuntos += 85;
                    } elseif ($evaluacion === 'B') {
                        $sumaPuntos += 60;
                    }
                }
            }

            $promedioAula = $totalEvaluados > 0 ? round($sumaPuntos / $totalEvaluados) : 0;

            return view('academico.apoyo_familiar.index', compact('aula', 'matriculas', 'corteActivo', 'promedioAula', 'totalEvaluados'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un error al cargar las evaluaciones de Apoyo Familiar.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'corte_evaluativo_id' => ['required', 'integer', 'exists:corte_evaluativo,id'],
            'evaluaciones' => ['required', 'array'],
            'evaluaciones.*' => ['nullable', 'in:B,MB,EX'],
        ]);

        $corteId = (int) $request->corte_evaluativo_id;

        DB::beginTransaction();
        try {
            $now = now();
            foreach ($request->evaluaciones as $matriculaId => $valor) {
                if ($valor) {
                    DB::table('evaluacion_familiar')->updateOrInsert(
                        [
                            'matricula_id' => (int) $matriculaId,
                            'corte_evaluativo_id' => $corteId,
                        ],
                        [
                            'evaluacion' => $valor,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }
            DB::commit();

            return back()->with('success', 'Evaluaciones guardadas.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $this->formatearError($e, 'Ocurrió un problema al intentar guardar las evaluaciones.'));
        }
    }
}