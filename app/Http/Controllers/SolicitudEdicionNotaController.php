<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\SolicitudEdicionNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudEdicionNotaController extends Controller
{
    /**
     * Solo Dirección y Subdirección pueden gestionar las solicitudes de desbloqueo.
     */
    protected function autorizar(): void
    {
        if (!Auth::user()->hasAnyRole(['Director', 'Subdirector'])) {
            abort(403, 'No tiene permisos para gestionar solicitudes de edición de notas.');
        }
    }

    /**
     * Lista de solicitudes pendientes y resueltas.
     */
    public function index(Request $request)
    {
        $this->autorizar();

        $estado = $request->query('estado', 'Pendiente');

        $solicitudes = SolicitudEdicionNota::with(['docente.usuario', 'nota.matricula.alumno', 'nota.aulaAsignaturaDocente.asignatura', 'autorizadoPor'])
            ->when($estado !== 'Todas', fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return view('academico.notas.solicitudes', compact('solicitudes', 'estado'));
    }

    /**
     * Aprueba una solicitud y desbloquea todas las notas del parcial (asignación + corte).
     */
    public function aprobar(SolicitudEdicionNota $solicitud)
    {
        $this->autorizar();

        $notaReferencia = $solicitud->nota;
        if ($notaReferencia) {
            // Desbloqueamos TODAS las notas del mismo parcial (asignación + corte)
            Nota::where('aula_asignatura_docente_id', $notaReferencia->aula_asignatura_docente_id)
                ->where('corte_evaluativo_id', $notaReferencia->corte_evaluativo_id)
                ->update(['bloqueado' => false]);
        }

        $solicitud->update([
            'estado' => 'Aprobada',
            'autorizado_por' => Auth::id(),
            'fecha_resolucion' => now(),
        ]);

        return back()->with('success', 'Solicitud aprobada. El parcial fue desbloqueado para edición.');
    }

    /**
     * Rechaza una solicitud de desbloqueo.
     */
    public function rechazar(Request $request, SolicitudEdicionNota $solicitud)
    {
        $this->autorizar();

        $solicitud->update([
            'estado' => 'Rechazada',
            'autorizado_por' => Auth::id(),
            'fecha_resolucion' => now(),
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }
}