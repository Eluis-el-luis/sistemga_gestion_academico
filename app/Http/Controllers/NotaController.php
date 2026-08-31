<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\IndicadorLogro;
use App\Models\AulaAsignaturaDocente;
use App\Http\Requests\StoreNotaRequest;
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

    public function index(Request $request)
    {
        $usuario = auth()->user();
        
        if ($usuario->hasRole(['Subdirector', 'Director', 'Coordinador', 'Gestor de Usuarios'])) {
            $modoSupervision = true;
            
            // 1. Grados ordenados jerárquicamente por modalidad e ID
            $grados = \App\Models\Grado::with('modalidad')
                        ->orderBy('modalidad_id', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
                        
            $aulas = \App\Models\Aula::with('grado')->get();
            
            // 2. Lógica de Pre-selección: Si no hay request, tomamos el primer grado
            $gradoSeleccionadoId = $request->filled('grado_id') ? $request->grado_id : ($grados->first()->id ?? null);
            
            // 3. Pre-selección de Aula: Si no hay request, tomamos la primera aula (Sección A) del grado seleccionado
            $aulaSeleccionadaId = $request->filled('aula_id') 
                ? $request->aula_id 
                : ($aulas->where('grado_id', $gradoSeleccionadoId)->first()->id ?? null);
            
            // 4. Consulta final
            $asignaciones = collect();
            if ($aulaSeleccionadaId) {
                $asignaciones = \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario'])
                    ->where('aula_id', $aulaSeleccionadaId)
                    ->get();
            }
        } 
        else {
            $modoSupervision = false;
            $grados = collect();
            $aulas = collect();
            $gradoSeleccionadoId = null;
            $aulaSeleccionadaId = null;
            
            $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
            $asignaciones = $docente 
                ? \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])->where('docente_id', $docente->id)->get() 
                : collect();
        }

        return view('academico.notas.index', compact('asignaciones', 'modoSupervision', 'grados', 'aulas', 'gradoSeleccionadoId', 'aulaSeleccionadaId'));
    }

    public function store(StoreNotaRequest $request)
    {
        $datos = $request->validated();
        $aulaAsignatura = AulaAsignaturaDocente::findOrFail($datos['aula_asignatura_docente_id']);
        $this->authorize('calificar', $aulaAsignatura);

        DB::transaction(function () use ($datos) {
            foreach ($datos['notas'] as $item) {
                $notaCuantitativa = $item['nota_cuantitativa'] ?? null;
                $codigoIndicador = $this->notaService->calcularIndicadorLogro($notaCuantitativa);
                
                $indicadorId = null;
                if ($codigoIndicador) {
                    $indicadorId = IndicadorLogro::where('codigo', $codigoIndicador)->value('id');
                }

                Nota::updateOrCreate(
                    [
                        'matricula_id' => $item['matricula_id'],
                        'aula_asignatura_docente_id' => $datos['aula_asignatura_docente_id'],
                        'corte_evaluativo_id' => $datos['corte_evaluativo_id'],
                    ],
                    [
                        'nota_cuantitativa' => $notaCuantitativa,
                        'indicador_logro_id' => $indicadorId,
                    ]
                );
            }
        });

        return back()->with('success', 'Planilla de calificaciones procesada y guardada exitosamente.');
    }

    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        $this->authorize('calificar', $asignacion);

        $cortes = \App\Models\CorteEvaluativo::whereHas('anioEscolar', function($q) {
            $q->where('activo', true);
        })->get();

        $corteSeleccionado = $request->query('corte_evaluativo_id', $cortes->first()->id ?? null);

        $matriculas = \App\Models\Matricula::with(['alumno', 'notas' => function($query) use ($asignacion, $corteSeleccionado) {
            $query->where('aula_asignatura_docente_id', $asignacion->id)
                  ->where('corte_evaluativo_id', $corteSeleccionado);
        }, 'notas.indicadorLogro'])
        ->where('aula_id', $asignacion->aula_id)
        ->where('estado', 'activo')
        ->get()
        ->sortBy(function($matricula) {
            return $matricula->alumno->nombre_completo;
        });

        $asignacion->load('aula.grado', 'asignatura');

        return view('academico.notas.planilla', compact('asignacion', 'cortes', 'corteSeleccionado', 'matriculas'));
    }
}