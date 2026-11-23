<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaPersonal;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaPersonalController extends Controller
{
    public function index(Request $request)
    {
        try {
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $mes = (string) $request->get('mes', now()->timezone('America/Managua')->format('Y-m'));
            $anio = date('Y', strtotime($mes));
            $mesNumerico = date('m', strtotime($mes));

            $asistencias = AsistenciaPersonal::where('usuario_id', $usuario->id)
                ->whereYear('fecha', $anio)
                ->whereMonth('fecha', $mesNumerico)
                ->orderByDesc('fecha')
                ->get();

            $totalPresentes = $asistencias->where('estado', 'Presente')->count();
            $totalRetardos = $asistencias->where('estado', 'Retardo')->count();
            $totalAusencias = $asistencias->whereIn('estado', ['Ausente', 'Justificado'])->count();

            return view('academico.asistencia.personal.index', compact(
                'asistencias',
                'totalPresentes',
                'totalRetardos',
                'totalAusencias'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar tu historial de asistencia.'));
        }
    }

    public function marcarLlegada(Request $request)
    {
        $usuarioId = Auth::id();
        $ahora = Carbon::now('America/Managua');
        $fechaHoy = $ahora->toDateString();
        $horaActual = $ahora->format('H:i:s');

        $horaOficial = Carbon::createFromTime(7, 0, 0, 'America/Managua');
        $limiteRetardo = $horaOficial->copy()->addHour();
        $margenPresente = $horaOficial->copy()->addMinutes(15);

        if ($ahora->greaterThan($margenPresente)) {
            $request->validate([
                'observaciones' => ['required', 'string', 'min:5', 'max:255'],
            ], [
                'observaciones.required' => 'Llegaste después de las 7:15 AM. Es obligatorio escribir una justificación.',
            ]);
        }

        try {
            $asistenciaExistente = AsistenciaPersonal::where('usuario_id', $usuarioId)
                ->where('fecha', $fechaHoy)
                ->first();

            if ($asistenciaExistente) {
                return back()->with('error', 'Ya has registrado tu asistencia el día de hoy.');
            }

            if ($ahora->greaterThan($limiteRetardo)) {
                $estado = 'Ausente';
                $mensaje = 'Has marcado fuera del margen permitido. Retardo grave registrado.';
            } elseif ($ahora->greaterThan($margenPresente)) {
                $estado = 'Retardo';
                $mensaje = 'Entrada registrada con retardo. Justificación guardada.';
            } else {
                $estado = 'Presente';
                $mensaje = 'Entrada registrada exitosamente a tiempo.';
            }

            AsistenciaPersonal::create([
                'usuario_id' => $usuarioId,
                'fecha' => $fechaHoy,
                'hora_entrada' => $horaActual,
                'turno' => (int) $ahora->format('H') < 12 ? 'Matutino' : 'Vespertino',
                'estado' => $estado,
                'observaciones' => $request->observaciones ?? null,
            ]);

            return back()->with('success', $mensaje);
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Ocurrió un error técnico al registrar tu asistencia.'));
        }
    }
}