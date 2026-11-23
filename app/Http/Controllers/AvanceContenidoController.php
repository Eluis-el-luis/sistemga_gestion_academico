<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\AvanceContenido;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvanceContenidoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', AvanceContenido::class);

            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $esSupervisorGlobal = $usuario->hasAnyRole(['directora', 'subdirectora', 'coordinador', 'Director', 'Subdirector', 'Coordinador']);
            $esDocenteGuia = $usuario->hasAnyRole(['docente_guia', 'Docente Guia']);

            if ($esSupervisorGlobal) {
                $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente'])->get();
                $modoSupervision = true;
            } elseif ($esDocenteGuia) {
                $docente = $usuario->docente;
                $aulaGuia = $docente
                    ? Aula::where('docente_guia_id', $docente->id)
                        ->whereHas('anioEscolar', fn ($q) => $q->where('estado', 'activo'))
                        ->first()
                    : null;

                $query = AulaAsignaturaDocente::with(['aula.grado', 'asignatura']);
                if ($docente) {
                    $query->where('docente_id', $docente->id);
                }
                if ($aulaGuia) {
                    $query->orWhere('aula_id', $aulaGuia->id);
                }

                $asignaciones = $query->get();
                $modoSupervision = false;
            } else {
                $docente = $usuario->docente;
                $asignaciones = $docente
                    ? AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->where('docente_id', $docente->id)->get()
                    : collect();
                $modoSupervision = false;
            }

            if ($asignaciones->isEmpty()) {
                return view('academico.avance.index', [
                    'asignaciones' => collect(),
                    'asignacionSeleccionada' => null,
                    'avances' => collect(),
                    'modoSupervision' => $modoSupervision,
                    'cortes' => collect(),
                ]);
            }

            $asignacionId = $request->query('asignacion_id', $asignaciones->first()->id);
            $asignacionSeleccionada = $asignaciones->firstWhere('id', $asignacionId) ?? $asignaciones->first();
            $avances = $asignacionSeleccionada
                ? AvanceContenido::where('aula_asignatura_docente_id', $asignacionSeleccionada->id)->orderBy('mes')->get()
                : collect();

            return view('academico.avance.index', compact(
                'asignaciones',
                'modoSupervision',
                'asignacionSeleccionada',
                'avances'
            ));
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', $this->formatearError($e, 'Ocurrió un problema al cargar el panel de avance de contenidos.'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'aula_asignatura_docente_id' => ['required', 'integer', 'exists:aula_asignatura_docente,id'],
            'mes' => ['required', 'date_format:Y-m'],
            'porcentaje_avance' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $this->authorize('create', AvanceContenido::class);

            $asignacion = AulaAsignaturaDocente::findOrFail($request->aula_asignatura_docente_id);
            /** @var Usuario $usuario */
            $usuario = Auth::user();
            $docente = $usuario->docente;

            if ($docente && (int) $asignacion->docente_id !== (int) $docente->id
                && !$usuario->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector'])) {
                return back()->with('error', 'No tienes permiso para registrar el avance de una asignación que no impartes.');
            }

            AvanceContenido::updateOrCreate(
                [
                    'aula_asignatura_docente_id' => $asignacion->id,
                    'mes' => $request->mes,
                ],
                [
                    'porcentaje_avance' => $request->porcentaje_avance,
                ]
            );

            return back()->with('success', 'Avance de contenidos registrado correctamente.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $this->formatearError($e, 'Hubo un error técnico al intentar guardar el avance.'));
        }
    }

    public function destroy(AvanceContenido $avance)
    {
        try {
            $this->authorize('delete', $avance);
            $avance->delete();

            return back()->with('success', 'Registro de avance eliminado.');
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->with('error', $this->formatearError($e, 'No se pudo eliminar el registro de avance.'));
        }
    }
}