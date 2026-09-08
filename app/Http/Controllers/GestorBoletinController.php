<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\CorteEvaluativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GestorBoletinController extends Controller
{
    use AuthorizesRequests;

    public function bandeja(Request $request)
    {
        // Solo personal autorizado puede acceder a la bandeja de impresión
        // $this->authorize('viewBandeja', \App\Models\Boletin::class);

        $anioActivo = \App\Models\AnioEscolar::where('activo', true)->first();
        
        if (!$anioActivo) {
            return redirect()->route('dashboard')->with('error', 'No hay un año escolar activo configurado.');
        }

        // Obtener los cortes evaluativos (I Parcial, II Parcial, etc.)
        $cortes = CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('id')->get();
        
        // Si el usuario no selecciona un corte en el filtro, tomamos el primero por defecto
        $corteActivo = $request->input('corte_id', $cortes->first()->id ?? null);

        // Consultar todas las aulas y verificar su estado de envío
        $aulas = Aula::with(['grado', 'docenteGuia.usuario'])
            ->where('anio_escolar_id', $anioActivo->id)
            ->get()
            ->map(function ($aula) use ($corteActivo) {
                // Buscamos si existe un registro de envío/aprobación para esta aula y este corte
                // (Asumiendo una tabla 'envio_boletines' que registra el Visto Bueno del maestro)
                $envio = DB::table('envio_boletines')
                    ->where('aula_id', $aula->id)
                    ->where('corte_evaluativo_id', $corteActivo)
                    ->first();

                // Inyectamos el estado para que la vista decida qué colores y botones mostrar
                $aula->estado_impresion = $envio ? $envio->estado : 'Pendiente'; // Pendiente, Autorizado, Impreso
                $aula->fecha_autorizacion = $envio ? $envio->updated_at : null;

                return $aula;
            })
            // Ordenar para que las aulas "Autorizadas" (listas para imprimir) aparezcan de primero
            ->sortByDesc(function ($aula) {
                return $aula->estado_impresion === 'Autorizado' ? 1 : 0;
            })
            ->values();

        return view('academico.boletin.bandeja', compact('aulas', 'cortes', 'corteActivo', 'anioActivo'));
    }
}