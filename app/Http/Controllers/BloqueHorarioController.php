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
        
        // Ordenamos por la nueva jerarquía: Modalidad > Turno > Jornada > Número de bloque > Hora inicio
        $bloques = BloqueHorario::with('modalidad')
                    ->orderBy('modalidad_id')
                    ->orderBy('turno')
                    ->orderBy('tipo_jornada')
                    ->orderBy('numero_bloque')
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
            'tipo_jornada' => 'required|string',
            'numero_bloque' => 'nullable|integer|min:1|max:20',
            'nombre' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        BloqueHorario::create([
            'modalidad_id' => $request->modalidad_id,
            'turno' => $request->turno,
            'tipo_jornada' => $request->tipo_jornada,
            'numero_bloque' => $request->numero_bloque,
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