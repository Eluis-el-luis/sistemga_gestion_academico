<?php

namespace App\Http\Controllers;

use App\Models\CorteCerrado;
use App\Models\SolicitudEdicionNota;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SolicitudEdicionNotaController extends Controller
{
    protected function autorizar(): void
    {
        /** @var Usuario|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
            abort(403, 'No tiene permisos para gestionar solicitudes de edición de notas.');
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->autorizar();

            $estado = (string) $request->query('estado', 'Pendiente');

            $solicitudes = SolicitudEdicionNota::with([
                'docente.usuario',
                'nota.matricula.alumno',
                'nota.aulaAsignaturaDocente.asignatura',
                'autorizadoPor',
            ])
                ->when($estado !== 'Todas', fn ($q) => $q->where('estado', $estado))
                ->orderByDesc('created_at')
                ->get();

            return view('academico.notas.solicitudes', compact('solicitudes', 'estado'));
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar las solicitudes de edición de notas.'));
        }
    }

    public function aprobar(SolicitudEdicionNota $solicitud): RedirectResponse
    {
        try {
            $this->autorizar();

            DB::transaction(function () use ($solicitud) {
                $notaReferencia = $solicitud->nota;
                if ($notaReferencia) {
                    CorteCerrado::where('aula_asignatura_docente_id', $notaReferencia->aula_asignatura_docente_id)
                        ->where('corte_evaluativo_id', $notaReferencia->corte_evaluativo_id)
                        ->delete();
                }

                $solicitud->update([
                    'estado' => 'Aprobada',
                    'autorizado_por' => Auth::id(),
                    'fecha_resolucion' => now(),
                ]);
            });

            return back()->with('success', 'Solicitud aprobada. El parcial fue desbloqueado para edición.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible aprobar la solicitud de desbloqueo.'));
        }
    }

    public function rechazar(Request $request, SolicitudEdicionNota $solicitud): RedirectResponse
    {
        try {
            $this->autorizar();

            $solicitud->update([
                'estado' => 'Rechazada',
                'autorizado_por' => Auth::id(),
                'fecha_resolucion' => now(),
            ]);

            return back()->with('success', 'Solicitud rechazada.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()
                ->with('error', $this->formatearError($e, 'No fue posible rechazar la solicitud de desbloqueo.'));
        }
    }
}