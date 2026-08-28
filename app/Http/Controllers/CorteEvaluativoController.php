<?php

namespace App\Http\Controllers;

use App\Models\CorteEvaluativo;
use App\Models\AnioEscolar;
use Illuminate\Http\Request;

class CorteEvaluativoController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            abort(403, 'No tienes permisos para configurar los parámetros de evaluación.');
        }

        $anioActivo = AnioEscolar::where('activo', true)->first();
        
        $cortes = $anioActivo 
            ? CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('numero')->get() 
            : collect();

        return view('academico.cortes.index', compact('cortes', 'anioActivo'));
    }

    public function update(Request $request, CorteEvaluativo $corte)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'peso_acumulado' => 'required|integer|min:0|max:100',
            'peso_examen' => 'required|integer|min:0|max:100',
        ], [
            'fecha_fin.after' => 'La fecha de fin no puede ser anterior ni igual a la fecha de inicio.',
        ]);

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
    }
}