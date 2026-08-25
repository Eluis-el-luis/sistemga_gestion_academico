<?php

namespace App\Http\Controllers;

use App\Models\CorteEvaluativo;
use App\Models\AnioEscolar;
use Illuminate\Http\Request;

class CorteEvaluativoController extends Controller
{
    public function index()
    {
        // Seguridad: Solo roles administrativos pueden entrar aquí
        if (!auth()->user()->hasRole(['Director', 'Subdirector', 'Gestor de Usuarios'])) {
            abort(403, 'No tienes permisos para configurar los parámetros de evaluación.');
        }

        $anioActivo = AnioEscolar::where('activo', true)->first();
        
        // Traemos los cortes ordenados por número (1er Parcial, 2do Parcial...)
        $cortes = $anioActivo 
            ? CorteEvaluativo::where('anio_escolar_id', $anioActivo->id)->orderBy('numero')->get() 
            : collect();

        return view('academico.cortes.index', compact('cortes', 'anioActivo'));
    }

    public function update(Request $request, CorteEvaluativo $corte)
    {
        $request->validate([
            'peso_acumulado' => 'required|integer|min:0|max:100',
            'peso_examen' => 'required|integer|min:0|max:100',
        ]);

        // REGLA DE NEGOCIO: La suma debe ser exactamente 100
        $suma = $request->peso_acumulado + $request->peso_examen;
        if ($suma !== 100) {
            return back()->with('error', "La suma del Acumulado y el Examen debe ser exactamente 100. Actualmente suma: {$suma}");
        }

        $corte->update([
            'peso_acumulado' => $request->peso_acumulado,
            'peso_examen' => $request->peso_examen
        ]);

        return back()->with('success', 'Pesos evaluativos actualizados correctamente.');
    }
}