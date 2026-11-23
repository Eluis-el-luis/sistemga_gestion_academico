<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\Aula;
use App\Models\Boletin;
use App\Models\CorteEvaluativo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GestorBoletinController extends Controller
{
    use AuthorizesRequests;

    public function bandeja(Request $request)
    {
        try {
            $this->authorize('viewAny', Boletin::class);

            $anioActivo = AnioEscolar::where('estado', 'activo')->first();
            if (!$anioActivo) {
                return redirect()->route('dashboard')->with('error', 'No hay un año escolar activo configurado.');
            }

            $cortes = CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('id')->get();
            if ($cortes->isEmpty()) {
                return view('academico.boletin.bandeja', [
                    'aulas' => collect(),
                    'cortes' => collect(),
                    'corteActivo' => null,
                    'anioActivo' => $anioActivo,
                ]);
            }

            $corteActivo = $request->input('corte_id', $cortes->first()->id);

            $aulas = Aula::with(['grado', 'docenteGuia.usuario'])
                ->where('anio_escolar_id', $anioActivo->id)
                ->get()
                ->map(function ($aula) use ($corteActivo) {
                    $envio = DB::table('envio_boletines')
                        ->where('aula_id', $aula->id)
                        ->where('corte_evaluativo_id', $corteActivo)
                        ->first();

                    $aula->estado_impresion = $envio->estado ?? 'Pendiente';
                    $aula->fecha_autorizacion = $envio->updated_at ?? null;

                    return $aula;
                })
                ->sortByDesc(fn ($aula) => $aula->estado_impresion === 'Autorizado' ? 1 : 0)
                ->values();

            return view('academico.boletin.bandeja', compact('aulas', 'cortes', 'corteActivo', 'anioActivo'));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al intentar cargar la bandeja de impresión de boletines.'));
        }
    }
}