<?php

namespace App\Http\Controllers;

use App\Models\AvanceContenido;
use App\Models\AulaAsignaturaDocente;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AvanceContenidoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Pantalla principal de seguimiento de contenidos: muestra las asignaturas
     * del docente y su avance por mes.
     * 
     * FIX CVE-2026-003: Validar ownership del asignacion_id solicitado
     * FIX CVE-2026-004: Incluir Docente Guía en modo supervisión y corregir query
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', AvanceContenido::class);

            $usuario = auth()->user();

            // FIX CVE-2026-004: Incluir 'Docente Guia' en roles de supervisión
            // pero con alcance limitado a su aula (no todas las asignaciones del sistema)
            $esSupervisorGlobal = $usuario->hasRole(['Subdirector', 'Director', 'Coordinador']);
            $esDocenteGuia = $usuario->hasRole('Docente Guia');

            if ($esSupervisorGlobal) {
                // Supervisores globales (Dirección/Coordinación) ven TODO
                $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente'])->get();
                $modoSupervision = true;
            } elseif ($esDocenteGuia) {
                // FIX CVE-2026-004: Docente Guía ve sus materias (docente_id) + materias de su aula (docente_guia_id)
                $docente = $usuario->docente;
                $aulaGuia = $docente 
                    ? Aula::where('docente_guia_id', $docente->id)
                          ->whereHas('anioEscolar', fn($q) => $q->where('estado', 'activo'))
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
                $modoSupervision = false; // No es supervisor global, pero ve su alcance
            } else {
                // Docente por Asignatura u otros: solo sus materias directas
                $docente = $usuario->docente;
                $asignaciones = $docente
                    ? AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->where('docente_id', $docente->id)->get()
                    : collect();
                $modoSupervision = false;
            }

            // Manejar caso sin asignaciones
            if ($asignaciones->isEmpty()) {
                return view('academico.avance-contenido.index', [
                    'asignaciones' => collect(),
                    'asignacionSeleccionada' => null,
                    'avances' => collect(),
                    'modoSupervision' => $modoSupervision,
                    'cortes' => collect(),
                ]);
            }

            $asignacionSeleccionada = null;
            $avances = collect();

            $asignacionId = $request->query('asignacion_id', $asignaciones->first()->id);

            if ($asignacionId) {
                // FIX CVE-2026-003: Validar que el asignacion_id solicitado esté en la colección permitida
                // Usar firstWhere sobre $asignaciones (ya filtrada) en lugar de find() global
                $asignacionSeleccionada = $asignaciones->firstWhere('id', $asignacionId);
                
                // Si el ID solicitado no está en sus asignaciones permitidas, usar la primera por defecto
                if (!$asignacionSeleccionada) {
                    $asignacionSeleccionada = $asignaciones->first();
                    $asignacionId = $asignacionSeleccionada?->id;
                }

                if ($asignacionSeleccionada) {
                    $avances = AvanceContenido::where('aula_asignatura_docente_id', $asignacionId)
                        ->orderBy('mes')
                        ->get();
                }
            }

            return view('academico.avance.index', compact(
                'asignaciones', 'modoSupervision', 'asignacionSeleccionada', 'avances'
            ));

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla alguna consulta al armar el panel
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar el panel de avance de contenidos. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Registra o actualiza el avance de contenidos de una asignación en un mes específico.
     */
    public function store(Request $request)
    {
        // Validación AFUERA del try-catch para no interferir con el formulario
        $request->validate([
            'aula_asignatura_docente_id' => 'required|exists:aula_asignatura_docente,id',
            'mes' => 'required|date_format:Y-m',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $this->authorize('create', AvanceContenido::class);

            // Autorización de alcance: solo el docente dueño de la asignación (o supervisor)
            $asignacion = AulaAsignaturaDocente::findOrFail($request->aula_asignatura_docente_id);

            $docente = auth()->user()->docente;
            if ($docente && $asignacion->docente_id !== $docente->id
                && !auth()->user()->hasRole(['Director', 'Subdirector'])) {
                
                // Reemplazamos el abort(403) por una alerta elegante en la interfaz
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

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la base de datos rechaza la inserción
            return back()->withInput()->with('error', 'Hubo un error técnico al intentar guardar el avance. Verifica los datos e intenta de nuevo.');
        }
    }

    /**
     * Elimina un registro de avance.
     */
    public function destroy(AvanceContenido $avance)
    {
        try {
            $this->authorize('delete', $avance);
            $avance->delete();

            return back()->with('success', 'Registro de avance eliminado.');

        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si la BD bloquea la eliminación
            return back()->with('error', 'No se pudo eliminar el registro de avance. Es posible que el sistema lo esté protegiendo.');
        }
    }
}