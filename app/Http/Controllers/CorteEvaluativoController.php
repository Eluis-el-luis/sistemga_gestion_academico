<?php

namespace App\Http\Controllers;

use App\Models\CorteEvaluativo;
use App\Models\AnioEscolar;
use Illuminate\Http\Request;

class CorteEvaluativoController extends Controller
{
    public function index()
    {
        // UX: Cambiamos el abort(403) por una redirección amigable con alerta
        if (!auth()->user()->hasRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para configurar los parámetros de evaluación.');
        }

        try {
            $anioActivo = AnioEscolar::where('activo', true)->first();
            
            $cortes = $anioActivo 
                ? CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('numero')->get() 
                : collect();

            return view('academico.cortes.index', compact('cortes', 'anioActivo'));

        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla la conexión a la base de datos al buscar el año activo
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al intentar cargar la configuración de los cortes evaluativos.');
        }
    }

    public function update(Request $request, CorteEvaluativo $corte)
    {
        // Validación AFUERA para que el usuario vea los textos rojos si pone fechas incorrectas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'peso_acumulado' => 'required|integer|min:0|max:100',
            'peso_examen' => 'required|integer|min:0|max:100',
        ], [
            'fecha_fin.after' => 'La fecha de fin no puede ser anterior ni igual a la fecha de inicio.',
        ]);

        try {
            $suma = $request->peso_acumulado + $request->peso_examen;
            if ($suma !== 100) {
                return back()->with('error', "La suma del Acumulado y el Examen debe ser exactamente 100 puntos. Has enviado: {$suma} puntos.");
            }

            $corte->update([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'peso_acumulado' => $request->peso_acumulado,
                'peso_examen' => $request->peso_examen
            ]);

            return back()->with('success', 'Parámetros del corte evaluativo actualizados correctamente.');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si la base de datos rechaza la actualización
            return back()->withInput()->with('error', 'Hubo un problema técnico al intentar actualizar los parámetros del corte. Por favor, intenta de nuevo.');
        }
    }
}