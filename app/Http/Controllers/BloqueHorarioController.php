<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BloqueHorarioController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        try {
            $this->authorize('horarios.gestionar');

            $modalidades = Modalidad::all();
            
            $bloques = BloqueHorario::with('modalidad')
                        ->orderBy('modalidad_id')
                        ->orderBy('turno')
                        ->orderBy('tipo_jornada')
                        ->orderBy('hora_inicio')
                        ->get();

            return view('academico.bloques.index', compact('modalidades', 'bloques'));
            
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la consulta a la BD falla
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al intentar cargar la configuración de bloques horarios.');
        }
    }

    // 1. Guardado individual inteligente (Autoincremento)
    public function store(Request $request)
    {
        // Validación AFUERA del try-catch
        $request->validate([
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string',
            'tipo_jornada' => 'required|string',
            'numero_bloque' => 'nullable|integer|min:1|max:20',
            'nombre' => 'required|string|max:50',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        try {
            $this->authorize('horarios.gestionar');

            $numeroBloque = $request->numero_bloque;

            // Si es una clase regular y no se proporcionó número, autoincrementamos
            if (!$request->boolean('es_recreo') && is_null($numeroBloque)) {
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
                'es_recreo' => $request->boolean('es_recreo'),
            ]);

            return back()->with('success', 'Bloque de horario oficial agregado correctamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Falla de inserción en la tabla
            return back()->withInput()->with('error', 'Hubo un error técnico al intentar guardar el bloque horario. Inténtalo nuevamente.');
        }
    }

    public function destroy(BloqueHorario $bloque)
    {
        try {
            $this->authorize('horarios.gestionar');

            // Protección: no permitir borrar si hay clases programadas que usan este bloque
            $enUso = \App\Models\Horario::where('bloque_horario_id', $bloque->id)->exists();
            if ($enUso) {
                return back()->with('error', 'No se puede eliminar este bloque porque tiene clases programadas. Elimine primero esas clases del horario.');
            }

            $bloque->delete();
            return back()->with('success', 'Bloque eliminado del horario oficial.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Evita errores graves si el bloque está protegido por reglas de integridad en BD
            return back()->with('error', 'No se pudo eliminar el bloque horario. Es posible que existan datos asociados a él.');
        }
    }

    // 2. Clonación masiva de estructuras de tiempo
    public function clonar(Request $request)
    {
        // Validación AFUERA
        $request->validate([
            'origen_modalidad_id' => 'required|exists:modalidad,id',
            'origen_turno' => 'required|string',
            'origen_jornada' => 'required|string',
            'destino_modalidad_id' => 'required|exists:modalidad,id',
            'destino_turno' => 'required|string',
            'destino_jornada' => 'required|string',
        ]);

        DB::beginTransaction(); // Iniciamos transacción de seguridad
        try {
            $this->authorize('horarios.gestionar');
            
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
            
            DB::commit(); // Si todo sale bien, guardamos definitivamente
            return back()->with('success', count($nuevosBloques) . ' bloques clonados exitosamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack(); // Si hay error a la mitad, cancelamos todo para no ensuciar la base de datos
            return back()->with('error', 'Ocurrió un problema al clonar los bloques horarios. El proceso fue cancelado por seguridad.');
        }
    }

    // 3. Generador Matemático Masivo
    public function generarMasivo(Request $request)
    {
        // Validación AFUERA
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

        DB::beginTransaction(); // Iniciamos transacción de seguridad
        try {
            $this->authorize('horarios.gestionar');

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

            DB::commit(); // Todo generado correctamente
            return back()->with('success', 'Estructura horaria generada automáticamente.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack(); // Si fallan los cálculos matemáticos de hora, revertimos todo
            return back()->withInput()->with('error', 'Ocurrió un error matemático al generar los bloques horarios. Revisa las horas ingresadas.');
        }
    }

    // Eliminar una estructura completa de bloques (Jornada)
    public function destroyJornada(Request $request)
    {
        // Validación AFUERA
        $request->validate([
            'modalidad_id' => 'required|exists:modalidad,id',
            'turno' => 'required|string',
            'tipo_jornada' => 'required|string',
        ]);

        try {
            $this->authorize('horarios.gestionar');

            BloqueHorario::where('modalidad_id', $request->modalidad_id)
                ->where('turno', $request->turno)
                ->where('tipo_jornada', $request->tipo_jornada)
                ->delete();

            return back()->with('success', 'La jornada completa ha sido eliminada.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Por si hay restricciones de BD al intentar un borrado masivo
            return back()->with('error', 'No se pudo eliminar la jornada completa. Asegúrate de no tener clases asignadas a estos bloques.');
        }
    }
}