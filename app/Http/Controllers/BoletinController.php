<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Boletin;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\AulaAsignaturaDocente;
use App\Services\NotaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BoletinController extends Controller
{
    use AuthorizesRequests;

    protected $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    /**
     * Listado de aulas / alumnos para generar boletines.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Boletin::class);

        $usuario = auth()->user();

        $aulas = Aula::with(['grado', 'anioEscolar'])
            ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
            ->when($usuario->docente && !$usuario->hasRole(['Director', 'Subdirector']), function ($q) use ($usuario) {
                $q->where('docente_guia_id', $usuario->docente->id);
            })
            ->get();

        $aulaSeleccionada = $request->query('aula_id', $aulas->first()->id ?? null);

        $matriculas = collect();
        if ($aulaSeleccionada) {
            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aulaSeleccionada)
                ->where('estado', 'activo')
                ->orderBy('id')
                ->get();
        }

        return view('academico.boletin.index', compact('aulas', 'aulaSeleccionada', 'matriculas'));
    }

    /**
     * Muestra el boletín de un alumno (vista imprimible, se guarda como PDF vía el navegador).
     */
    public function show(Request $request, Matricula $matricula)
    {
        $this->authorize('view', $matricula);

        $matricula->load(['alumno', 'aula.grado', 'aula.modalidad', 'anioEscolar']);

        // Asignaturas de esta aula en el año activo
        $asignaciones = AulaAsignaturaDocente::with('asignatura')
            ->where('aula_id', $matricula->aula_id)
            ->get();

        $notas = Nota::with(['aulaAsignaturaDocente.asignatura', 'indicadorLogro'])
            ->where('matricula_id', $matricula->id)
            ->get();

        // Calcular nota y promedio por asignatura
        $detalle = [];
        foreach ($asignaciones as $asignacion) {
            $notasAsignatura = $notas->where('aula_asignatura_docente_id', $asignacion->id);
            $promedio = $notasAsignatura->avg('nota_cuantitativa');

            $detalle[] = [
                'asignatura' => $asignacion->asignatura->nombre,
                'promedio' => is_null($promedio) ? null : round($promedio, 2),
                'notas' => $notasAsignatura->values(),
            ];
        }

        $promediosValidos = array_filter(array_column($detalle, 'promedio'), fn ($p) => !is_null($p));
        $promedioGeneral = count($promediosValidos) > 0
            ? $this->notaService->calcularPromedioGeneral($promediosValidos)
            : 0.00;

        return view('academico.boletin.show', compact('matricula', 'detalle', 'promedioGeneral'));
    }
}