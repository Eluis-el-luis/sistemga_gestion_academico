<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use App\Models\Horario;
use App\Models\Modalidad;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al intentar cargar la configuración de bloques horarios.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'turno' => ['required', 'string'],
            'tipo_jornada' => ['required', 'string'],
            'numero_bloque' => ['nullable', 'integer', 'min:1', 'max:20'],
            'nombre' => ['required', 'string', 'max:50'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        try {
            $this->authorize('horarios.gestionar');

            $numeroBloque = $request->numero_bloque;
            if (!$request->boolean('es_recreo') && is_null($numeroBloque)) {
                $ultimoBloque = BloqueHorario::where('modalidad_id', $request->modalidad_id)
                    ->where('turno', $request->turno)
                    ->where('tipo_jornada', $request->tipo_jornada)
                    ->where('es_recreo', false)
                    ->max('numero_bloque');

                $numeroBloque = $ultimoBloque ? ((int) $ultimoBloque + 1) : 1;
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
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error técnico al intentar guardar el bloque horario.'));
        }
    }

    public function destroy(BloqueHorario $bloque)
    {
        try {
            $this->authorize('horarios.gestionar');

            if (Horario::where('bloque_horario_id', $bloque->id)->exists()) {
                return back()->with('error', 'No se puede eliminar este bloque porque tiene clases programadas.');
            }

            $bloque->delete();

            return back()->with('success', 'Bloque eliminado del horario oficial.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el bloque horario.'));
        }
    }

    public function clonar(Request $request)
    {
        $request->validate([
            'origen_modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'origen_turno' => ['required', 'string'],
            'origen_jornada' => ['required', 'string'],
            'destino_modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'destino_turno' => ['required', 'string'],
            'destino_jornada' => ['required', 'string'],
        ]);

        $this->authorize('horarios.gestionar');

        DB::beginTransaction();
        try {
            $bloquesOrigen = BloqueHorario::where('modalidad_id', $request->origen_modalidad_id)
                ->where('turno', $request->origen_turno)
                ->where('tipo_jornada', $request->origen_jornada)
                ->get();

            if ($bloquesOrigen->isEmpty()) {
                DB::rollBack();

                return back()->with('error', 'El horario de origen seleccionado está vacío.');
            }

            $nuevosBloques = [];
            $now = now();
            foreach ($bloquesOrigen as $bloque) {
                $nuevosBloques[] = [
                    'modalidad_id' => $request->destino_modalidad_id,
                    'turno' => $request->destino_turno,
                    'tipo_jornada' => $request->destino_jornada,
                    'numero_bloque' => $bloque->numero_bloque,
                    'nombre' => $bloque->nombre,
                    'hora_inicio' => $bloque->hora_inicio,
                    'hora_fin' => $bloque->hora_fin,
                    'es_recreo' => (bool) $bloque->es_recreo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            BloqueHorario::insert($nuevosBloques);
            DB::commit();

            return back()->with('success', count($nuevosBloques) . ' bloques clonados exitosamente.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $this->formatearError($e, 'Ocurrió un problema al clonar los bloques horarios.'));
        }
    }

    public function generarMasivo(Request $request)
    {
        $request->validate([
            'modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'turno' => ['required', 'string'],
            'tipo_jornada' => ['required', 'string'],
            'hora_inicio_base' => ['required', 'date_format:H:i'],
            'duracion_clase' => ['required', 'integer', 'min:15', 'max:120'],
            'cantidad_bloques' => ['required', 'integer', 'min:1', 'max:15'],
            'posicion_receso' => ['nullable', 'integer', 'min:1', 'max:15'],
            'duracion_receso' => ['nullable', 'integer', 'min:5', 'max:60'],
        ]);

        $this->authorize('horarios.gestionar');

        DB::beginTransaction();
        try {
            $horaActual = Carbon::createFromFormat('H:i', $request->hora_inicio_base);
            $contadorClases = 1;

            for ($i = 1; $i <= (int) $request->cantidad_bloques; $i++) {
                if ((int) $request->posicion_receso === $i && (int) $request->duracion_receso > 0) {
                    $horaFinReceso = (clone $horaActual)->addMinutes((int) $request->duracion_receso);

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
                    $horaActual = $horaFinReceso;
                }

                $horaFinClase = (clone $horaActual)->addMinutes((int) $request->duracion_clase);

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

                $horaActual = $horaFinClase;
                $contadorClases++;
            }

            DB::commit();

            return back()->with('success', 'Estructura horaria generada automáticamente.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error al generar los bloques horarios.'));
        }
    }

    public function destroyJornada(Request $request)
    {
        $request->validate([
            'modalidad_id' => ['required', 'integer', 'exists:modalidad,id'],
            'turno' => ['required', 'string'],
            'tipo_jornada' => ['required', 'string'],
        ]);

        try {
            $this->authorize('horarios.gestionar');

            BloqueHorario::where('modalidad_id', $request->modalidad_id)
                ->where('turno', $request->turno)
                ->where('tipo_jornada', $request->tipo_jornada)
                ->delete();

            return back()->with('success', 'La jornada completa ha sido eliminada.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar la jornada completa.'));
        }
    }
}