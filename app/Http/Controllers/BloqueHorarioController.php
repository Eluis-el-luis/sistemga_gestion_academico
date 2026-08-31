<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BloqueHorarioController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('horarios.gestionar');

        $modalidades = Modalidad::all();
        
        // Traemos los bloques ordenados cronológicamente
        $bloques = BloqueHorario::with('modalidad')
                    ->orderBy('modalidad_id')
                    ->orderBy('turno')
                    ->orderBy('hora_inicio')
                    ->get();

        return view('academico.bloques.index', compact('modalidades', 'bloques'));
    }

    public function store(Request $request)
    {
        $this->authorize('horarios.gestionar');

        $request->validate([
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string',
            'nombre' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        BloqueHorario::create([
            'modalidad_id' => $request->modalidad_id,
            'turno' => $request->turno,
            'nombre' => $request->nombre,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'es_recreo' => $request->has('es_recreo') ? true : false,
        ]);

        return back()->with('success', 'Bloque de horario oficial agregado correctamente.');
    }

    public function destroy(BloqueHorario $bloque)
    {
        $this->authorize('horarios.gestionar');
        $bloque->delete();
        return back()->with('success', 'Bloque eliminado del horario oficial.');
    }
}