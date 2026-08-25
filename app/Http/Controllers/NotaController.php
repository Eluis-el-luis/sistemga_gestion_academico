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

    // Inyectamos el servicio matemático en el constructor
    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    /**
     * Recibe la sábana de notas, calcula los indicadores y guarda todo en lote.
     */
    public function store(StoreNotaRequest $request)
    {
        $datos = $request->validated();

    
        $aulaAsignatura = AulaAsignaturaDocente::findOrFail($datos['aula_asignatura_docente_id']);
        $this->authorize('calificar', $aulaAsignatura);

    
        DB::transaction(function () use ($datos) {
            
            foreach ($datos['notas'] as $item) {
                
                $notaCuantitativa = $item['nota_cuantitativa'] ?? null;
                
                // A. Llamamos al Cerebro para saber la letra (AA, AS, AF, AI)
                $codigoIndicador = $this->notaService->calcularIndicadorLogro($notaCuantitativa);
                
                // B. Buscamos el ID de esa letra en la base de datos
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

        // Retornamos a la vista con el mensaje de éxito
        return back()->with('success', 'Planilla de calificaciones procesada y guardada exitosamente.');
    }

    /**
     * Pantalla principal del docente: Muestra sus materias asignadas para elegir cuál calificar.
     */
    public function index()
    {
        // Buscamos al docente asociado al usuario logueado
        $docente = \App\Models\Docente::where('usuario_id', auth()->id())->first();
        
        $asignaciones = collect();
        if ($docente) {
            // Traemos todas las materias que imparte, cargando los datos del aula y grado
            $asignaciones = \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])
                ->where('docente_id', $docente->id)
                ->get();
        }

        return view('academico.notas.index', compact('asignaciones'));
    }

    /**
     * Muestra la planilla de calificaciones para un aula y materia específica.
     */
    public function create(Request $request, AulaAsignaturaDocente $asignacion)
    {
        // 1. Autorización: ¿Es el profe asignado a esta materia o es el Director?
        $this->authorize('calificar', $asignacion);

        // 2. Traemos los cortes evaluativos del Año Escolar Activo
        $cortes = \App\Models\CorteEvaluativo::whereHas('anioEscolar', function($q) {
            $q->where('activo', true);
        })->get();

        // Si el profesor cambia el select, agarramos ese ID. Si no, usamos el primer corte por defecto.
        $corteSeleccionado = $request->query('corte_evaluativo_id', $cortes->first()->id ?? null);

        // 3. Cargamos a los estudiantes MATRICULADOS Y ACTIVOS en esta aula
        $matriculas = \App\Models\Matricula::with(['alumno', 'notas' => function($query) use ($asignacion, $corteSeleccionado) {
            // Solo traemos las notas de ESTA materia y ESTE corte evaluativo
            $query->where('aula_asignatura_docente_id', $asignacion->id)
                  ->where('corte_evaluativo_id', $corteSeleccionado);
        }, 'notas.indicadorLogro'])
        ->where('aula_id', $asignacion->aula_id)
        ->where('estado', 'activo')
        ->get()
        ->sortBy(function($matricula) {
            return $matricula->alumno->nombre_completo; // Ordenamos alfabéticamente
        });

        // Cargamos relaciones extra para la cabecera de la vista
        $asignacion->load('aula.grado', 'asignatura');

        return view('academico.notas.planilla', compact('asignacion', 'cortes', 'corteSeleccionado', 'matriculas'));
    }
}