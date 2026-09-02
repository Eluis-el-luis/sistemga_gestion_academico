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
        $fechaFiltro = $request->get('fecha', Carbon::now('America/Managua')->toDateString());
        $fechaFormateada = Carbon::parse($fechaFiltro)->format('d/m/y');
        
        // Excluimos a Director y Subdirector de la obligación de asistencia
        $personal = \App\Models\Usuario::with(['roles', 'asistencias' => function($query) use ($fechaFiltro) {
            $query->where('fecha', $fechaFiltro);
        }])->whereHas('roles', function($q) {
            $q->whereIn('name', ['Docente Guia', 'Docente por Asignatura']);
        })->get();

        return view('academico.asistencia.personal.index', compact('personal', 'fechaFiltro', 'fechaFormateada'));
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