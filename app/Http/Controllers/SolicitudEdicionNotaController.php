<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\SolicitudEdicionNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudEdicionNotaController extends Controller
{
    /**
     * Solo Dirección y Subdirección pueden gestionar las solicitudes de desbloqueo.
     */
    protected function autorizar(): void
    {
        if (!Auth::user()->hasAnyRole(['Director', 'Subdirector'])) {
            // UX: Lanzamos una excepción que será atrapada o redirigida amablemente
            abort(403, 'No tiene permisos para gestionar solicitudes de edición de notas.');
        }
    }

    /**
     * Lista de solicitudes pendientes y resueltas.
     */
    public function index(Request $request)
    {
        try {
            $this->autorizar();

            $estado = $request->query('estado', 'Pendiente');

            $solicitudes = SolicitudEdicionNota::with(['docente.usuario', 'nota.matricula.alumno', 'nota.aulaAsignaturaDocente.asignatura', 'autorizadoPor'])
                ->when($estado !== 'Todas', fn ($q) => $q->where('estado', $estado))
                ->orderByDesc('created_at')
                ->get();

            return view('academico.notas.solicitudes', compact('solicitudes', 'estado'));

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la consulta a la base de datos falla
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al cargar el buzón de solicitudes de edición.');
        }
    }

    /**
     * Aprueba una solicitud y desbloquea todas las notas del parcial (asignación + corte).
     */
    public function aprobar(SolicitudEdicionNota $solicitud)
    {
        DB::beginTransaction();
        try {
            $this->autorizar();

            $notaReferencia = $solicitud->nota;
            if ($notaReferencia) {
                // Desbloqueamos el parcial completo (asignación + corte)
                \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $notaReferencia->aula_asignatura_docente_id)
                    ->where('corte_evaluativo_id', $notaReferencia->corte_evaluativo_id)
                    ->delete();
            }

            $solicitud->update([
                'estado' => 'Aprobada',
                'autorizado_por' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Solicitud aprobada. El parcial fue desbloqueado para edición.');

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            DB::rollBack();
            // CONTINGENCIA: Si la transacción falla
            return back()->with('error', 'No se pudo aprobar la solicitud debido a un problema técnico. El cambio fue cancelado.');
        }
    }

    /**
     * Rechaza una solicitud de desbloqueo.
     */
    public function rechazar(Request $request, SolicitudEdicionNota $solicitud)
    {
        try {
            $this->autorizar();

            $solicitud->update([
                'estado' => 'Rechazada',
                'autorizado_por' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);

            return back()->with('success', 'Solicitud rechazada.');

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si el update falla
            return back()->with('error', 'Ocurrió un error técnico al intentar rechazar la solicitud.');
        }
    }
}