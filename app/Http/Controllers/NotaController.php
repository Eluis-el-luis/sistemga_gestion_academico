<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\IndicadorLogro;
use App\Models\AulaAsignaturaDocente;
use App\Models\NotaActividad;
use App\Services\NotaService;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NotaController extends Controller
{
    use AuthorizesRequests;

    protected $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    // 1. EL VISOR PRINCIPAL (Se mantiene casi igual, perfecto para navegación)
    public function index(Request $request)
    {
        $usuario = auth()->user();
        
        if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador', 'Gestor de Usuarios'])) {
            $modoSupervision = true;
            $grados = \App\Models\Grado::with('modalidad')->orderBy('modalidad_id', 'asc')->orderBy('id', 'asc')->get();
            $aulas = \App\Models\Aula::with('grado')->get();
            
            $gradoSeleccionadoId = $request->filled('grado_id') ? $request->grado_id : ($grados->first()->id ?? null);
            $aulaSeleccionadaId = $request->filled('aula_id') ? $request->aula_id : ($aulas->where('grado_id', $gradoSeleccionadoId)->first()->id ?? null);
            
            $asignaciones = $aulaSeleccionadaId 
                ? AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario'])->where('aula_id', $aulaSeleccionadaId)->get() 
                : collect();
        } else {
            $modoSupervision = false;
            $grados = collect(); $aulas = collect();
            $gradoSeleccionadoId = null; $aulaSeleccionadaId = null;
            
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
            $asignaciones = $docente 
                ? AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->where('docente_id', $docente->id)->get() 
                : collect();
        }

        return view('academico.notas.index', compact('asignaciones', 'modoSupervision', 'grados', 'aulas', 'gradoSeleccionadoId', 'aulaSeleccionadaId'));
    }

    // 2. LA PLANILLA (Ahora carga las Actividades y verifica si está bloqueada)
    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);

        $cortes = \App\Models\CorteEvaluativo::whereHas('anioEscolar', fn($q) => $q->where('activo', true))->get();
        $corteSeleccionado = $request->query('corte_evaluativo_id', $cortes->first()->id ?? null);

        // Traemos las actividades que el maestro configuró previamente para este parcial
        $actividades = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                            ->where('corte_evaluativo_id', $corteSeleccionado)
                            ->orderBy('fecha', 'asc')
                            ->get();

        // Verificamos si este parcial ya fue cerrado y bloqueado por el maestro
        $estaBloqueado = Nota::where('aula_asignatura_docente_id', $asignacion->id)
                            ->where('corte_evaluativo_id', $corteSeleccionado)
                            ->where('bloqueado', true)
                            ->exists();

        $matriculas = \App\Models\Matricula::with(['alumno', 'notas' => function($query) use ($asignacion, $corteSeleccionado) {
            $query->where('aula_asignatura_docente_id', $asignacion->id)
                  ->where('corte_evaluativo_id', $corteSeleccionado);
        }])
        ->where('aula_id', $asignacion->aula_id)
        ->where('estado', 'activo')
        ->get()
        ->sortBy(fn($m) => $m->alumno->nombre_completo);

        // Cargamos también las notas individuales de las actividades usando DB para cruzar rápido
        $notasActividades = DB::table('nota_actividad')
            ->whereIn('matricula_id', $matriculas->pluck('id'))
            ->whereIn('actividad_evaluativa_id', $actividades->pluck('id'))
            ->get()
            ->groupBy('matricula_id');

        $asignacion->load('aula.grado', 'asignatura');

        return view('academico.notas.planilla', compact('asignacion', 'cortes', 'corteSeleccionado', 'matriculas', 'actividades', 'notasActividades', 'estaBloqueado'));
    }

    // 3. AUTO-SUMA Y VALIDACIÓN ESTRICTA (Reemplaza el store anterior)
    public function store(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);
        
        $corteId = $request->corte_evaluativo_id;

        // BARRERA 1: ¿El parcial está bloqueado?
        if (Nota::where('aula_asignatura_docente_id', $asignacion->id)->where('corte_evaluativo_id', $corteId)->where('bloqueado', true)->exists()) {
            return back()->with('error', 'El parcial está cerrado. Solicita autorización para modificar.');
        }

        $actividades = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asignacion->id)
                            ->where('corte_evaluativo_id', $corteId)
                            ->get()->keyBy('id');

        DB::transaction(function () use ($request, $asignacion, $corteId, $actividades) {
            // El front-end enviará un arreglo: name="notas[matricula_id][actividad_id]"
            foreach ($request->notas as $matriculaId => $calificaciones) {
                
                $sumaTotalAlumno = 0;

                foreach ($calificaciones as $actividadId => $notaIngresada) {
                    if (is_null($notaIngresada)) continue;

                    $actividad = $actividades->get($actividadId);
                    if (!$actividad) continue;

                    // BARRERA 2: Limitar la nota al puntaje máximo de la actividad
                    $notaFinal = min(abs($notaIngresada), $actividad->puntaje_maximo);

                    // 1. Guardar la nota individual usando el modelo Eloquent
                    NotaActividad::updateOrCreate(
                        ['matricula_id' => $matriculaId, 'actividad_evaluativa_id' => $actividadId],
                        ['nota_obtenida' => $notaFinal]
                    );

                    $sumaTotalAlumno += $notaFinal;
                }

                // 2. Auto-Suma Global en la tabla 'nota' (centralizado en el Service)
                $this->notaService->registrarNotaFinal($matriculaId, $asignacion->id, $corteId, $sumaTotalAlumno);
            }
        });

        return back()->with('success', 'Calificaciones actualizadas. La auto-suma se ha calculado exitosamente.');
    }

    // 4. NUEVO: CERRAR PARCIAL (Congela las notas)
    public function cerrarParcial(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);
        
        Nota::where('aula_asignatura_docente_id', $asignacion->id)
            ->where('corte_evaluativo_id', $request->corte_evaluativo_id)
            ->update(['bloqueado' => true]);

        return back()->with('success', 'Calificaciones cerradas de forma permanente. Ya no pueden ser editadas.');
    }

    // 5. NUEVO: SOLICITAR DESBLOQUEO (Auditoría)
    public function solicitarDesbloqueo(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);
        
        $request->validate(['motivo' => 'required|string|max:500']);
        $docenteId = auth()->user()->docente->id ?? null;

        if (!$docenteId) abort(403);

        // Obtenemos una de las notas bloqueadas como referencia para la solicitud
        $notaReferencia = Nota::where('aula_asignatura_docente_id', $asignacion->id)
                              ->where('corte_evaluativo_id', $request->corte_evaluativo_id)
                              ->first();

        if ($notaReferencia) {
            \App\Models\SolicitudEdicionNota::updateOrCreate(
                [
                    'docente_id' => $docenteId,
                    'nota_id' => $notaReferencia->id,
                    'estado' => 'Pendiente',
                ],
                [
                    'motivo' => $request->motivo,
                ]
            );
        }

        return back()->with('success', 'Solicitud enviada. La Subdirección revisará tu petición pronto.');
    }
}