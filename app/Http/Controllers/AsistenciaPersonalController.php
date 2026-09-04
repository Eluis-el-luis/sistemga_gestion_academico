<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaPersonal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AsistenciaPersonalController extends Controller
{
    public function index(Request $request)
{
    /** @var \App\Models\Usuario $usuario */
    $usuario = auth()->user();

    // 1. Obtener el mes a consultar (Por defecto: mes actual)
    $mes = $request->get('mes', now()->timezone('America/Managua')->format('Y-m'));
    
    // Extraer año y mes numérico para la consulta SQL
    $anio = date('Y', strtotime($mes));
    $mesNumerico = date('m', strtotime($mes));

    // 2. Traer el historial exclusivo del usuario logueado
    $asistencias = \App\Models\AsistenciaPersonal::where('usuario_id', $usuario->id)
        ->whereYear('fecha', $anio)
        ->whereMonth('fecha', $mesNumerico)
        ->orderBy('fecha', 'desc')
        ->get();

    // 3. Calcular los KPIs de bolsillo
    $totalPresentes = $asistencias->where('estado', 'Presente')->count();
    $totalRetardos  = $asistencias->where('estado', 'Retardo')->count();
    $totalAusencias = $asistencias->whereIn('estado', ['Ausente', 'Justificado'])->count();

    return view('academico.asistencia.personal.index', compact(
        'asistencias', 
        'totalPresentes', 
        'totalRetardos', 
        'totalAusencias'
    ));
}

    public function marcarLlegada(Request $request)
    {
        $usuarioId = Auth::id();
        $ahora = Carbon::now('America/Managua');
        $fechaHoy = $ahora->toDateString();
        $horaActual = $ahora->format('H:i:s');

        $asistenciaExistente = AsistenciaPersonal::where('usuario_id', $usuarioId)
            ->where('fecha', $fechaHoy)
            ->first();

        if ($asistenciaExistente) {
            return back()->with('error', 'Ya has registrado tu asistencia el día de hoy.');
        }

        $horaOficial = Carbon::createFromTime(7, 0, 0, 'America/Managua');
        $limiteRetardo = $horaOficial->copy()->addHour(); // 8:00 AM
        $margenPresente = $horaOficial->copy()->addMinutes(15); // 7:15 AM

        // Si es más de las 7:15 AM, hacemos obligatoria la justificación
        if ($ahora->greaterThan($margenPresente)) {
            $request->validate([
                'observaciones' => 'required|string|min:5|max:255'
            ], [
                'observaciones.required' => 'Llegaste después de las 7:15 AM. Es obligatorio escribir una justificación.'
            ]);
        }

        if ($ahora->greaterThan($limiteRetardo)) {
            $estado = 'Ausente'; // Marcó después del margen máximo
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
            'estado' => $estado,
            'observaciones' => $request->observaciones ?? null,
        ]);

        return back()->with('success', $mensaje);
    }
}