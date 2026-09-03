<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class BloqueHorarioController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('horarios.gestionar');

        $modalidades = Modalidad::all();
        
        $bloques = BloqueHorario::with('modalidad')
                    ->orderBy('modalidad_id')
                    ->orderBy('turno')
                    ->orderBy('tipo_jornada')
                    ->orderBy('hora_inicio')
                    ->get();

        return view('academico.bloques.index', compact('modalidades', 'bloques'));
    }

    // 1. Guardado individual inteligente (Autoincremento)
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

        $numeroBloque = $request->numero_bloque;

        // Si es una clase regular y no se proporcionó número, autoincrementamos
        if (!$request->has('es_recreo') && is_null($numeroBloque)) {
            $ultimoBloque = BloqueHorario::where('modalidad_id', $request->modalidad_id)
                ->where('turno', $request->turno)
                ->where('tipo_jornada', $request->tipo_jornada)
                ->where('es_recreo', false)
                ->max('numero_bloque');
                
            $numeroBloque = $ultimoBloque ? $ultimoBloque + 1 : 1;
        }

        BloqueHorario::create([
            'modalidad_id' => $request->modalidad_id,
            'turno' => $request->turno,
            'tipo_jornada' => $request->tipo_jornada,
            'numero_bloque' => $numeroBloque,
            'nombre' => $request->nombre,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'es_recreo' => $request->has('es_recreo'),
        ]);

        return back()->with('success', 'Bloque de horario oficial agregado correctamente.');
    }

    public function destroy(BloqueHorario $bloque)
    {
        $this->authorize('horarios.gestionar');
        $bloque->delete();
        return back()->with('success', 'Bloque eliminado del horario oficial.');
    }

    // 2. Clonación masiva de estructuras de tiempo
    public function clonar(Request $request)
    {
        $this->authorize('horarios.gestionar');
        
        $request->validate([
            'origen_modalidad_id' => 'required|exists:modalidad,id',
            'origen_turno' => 'required|string',
            'origen_jornada' => 'required|string',
            'destino_modalidad_id' => 'required|exists:modalidad,id',
            'destino_turno' => 'required|string',
            'destino_jornada' => 'required|string',
        ]);

        $bloquesOrigen = BloqueHorario::where('modalidad_id', $request->origen_modalidad_id)
            ->where('turno', $request->origen_turno)
            ->where('tipo_jornada', $request->origen_jornada)
            ->get();

        if ($bloquesOrigen->isEmpty()) {
            return back()->with('error', 'El horario de origen seleccionado está vacío.');
        }

        $nuevosBloques = [];
        foreach ($bloquesOrigen as $bloque) {
            $nuevosBloques[] = [
                'modalidad_id' => $request->destino_modalidad_id,
                'turno' => $request->destino_turno,
                'tipo_jornada' => $request->destino_jornada,
                'numero_bloque' => $bloque->numero_bloque,
                'nombre' => $bloque->nombre,
                'hora_inicio' => $bloque->hora_inicio,
                'hora_fin' => $bloque->hora_fin,
                'es_recreo' => $bloque->es_recreo,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        BloqueHorario::insert($nuevosBloques);

        return back()->with('success', count($nuevosBloques) . ' bloques clonados exitosamente.');
    }

    // 3. Generador Matemático Masivo
    public function generarMasivo(Request $request)
    {
        $this->authorize('horarios.gestionar');

        $request->validate([
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string',
            'tipo_jornada' => 'required|string',
            'hora_inicio_base' => 'required|date_format:H:i',
            'duracion_clase' => 'required|integer|min:15|max:120',
            'cantidad_bloques' => 'required|integer|min:1|max:15',
            'posicion_receso' => 'nullable|integer|min:1|max:15',
            'duracion_receso' => 'nullable|integer|min:5|max:60'
        ]);

        $horaActual = Carbon::createFromFormat('H:i', $request->hora_inicio_base);
        $contadorClases = 1;

        for ($i = 1; $i <= $request->cantidad_bloques; $i++) {
            
            // Insertar recreo antes de este bloque si coincide la posición
            if ($request->posicion_receso == $i && $request->duracion_receso > 0) {
                $horaFinReceso = clone $horaActual;
                $horaFinReceso->addMinutes($request->duracion_receso);
                
                BloqueHorario::create([
                    'modalidad_id' => $request->modalidad_id,
                    'turno' => $request->turno,
                    'tipo_jornada' => $request->tipo_jornada,
                    'numero_bloque' => null,
                    'nombre' => 'Receso',
                    'hora_inicio' => $horaActual->format('H:i'),
                    'hora_fin' => $horaFinReceso->format('H:i'),
                    'es_recreo' => true,
                ]);
                $horaActual = clone $horaFinReceso;
            }

            // Insertar clase regular
            $horaFinClase = clone $horaActual;
            $horaFinClase->addMinutes($request->duracion_clase);

            BloqueHorario::create([
                'modalidad_id' => $request->modalidad_id,
                'turno' => $request->turno,
                'tipo_jornada' => $request->tipo_jornada,
                'numero_bloque' => $contadorClases,
                'nombre' => $contadorClases . 'ra Hora',
                'hora_inicio' => $horaActual->format('H:i'),
                'hora_fin' => $horaFinClase->format('H:i'),
                'es_recreo' => false,
            ]);

            $horaActual = clone $horaFinClase;
            $contadorClases++;
        }

        return back()->with('success', 'Estructura horaria generada automáticamente.');
    }

    // Eliminar una estructura completa de bloques (Jornada)
    public function destroyJornada(Request $request)
    {
        $this->authorize('horarios.gestionar');
        
        $request->validate([
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string',
            'tipo_jornada' => 'required|string',
        ]);

        BloqueHorario::where('modalidad_id', $request->modalidad_id)
            ->where('turno', $request->turno)
            ->where('tipo_jornada', $request->tipo_jornada)
            ->delete();

        return back()->with('success', 'La jornada completa ha sido eliminada.');
    }
}