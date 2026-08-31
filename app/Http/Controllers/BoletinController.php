<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Boletin;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\AulaAsignaturaDocente;
use App\Models\CorteEvaluativo;
use App\Models\AsistenciaAula;
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

        $matricula->load([
            'alumno',
            'aula.grado',
            'aula.modalidad',
            'aula.docenteGuia.usuario',
            'anioEscolar',
        ]);

        $anioEscolarId = $matricula->anio_escolar_id;

        // Cortes evaluativos del año (ordenados por número: I, II, III, IV)
        $cortes = CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)
            ->orderBy('numero')
            ->get();

        $corteActual = $request->query('corte_evaluativo_id')
            ? $cortes->firstWhere('id', $request->query('corte_evaluativo_id'))
            : $cortes->last();

        // Asignaturas de esta aula en el año activo
        $asignaciones = AulaAsignaturaDocente::with('asignatura')
            ->where('aula_id', $matricula->aula_id)
            ->where('anio_escolar_id', $anioEscolarId)
            ->get();

        // Notas del alumno en el año
        $notas = Nota::with(['aulaAsignaturaDocente.asignatura', 'indicadorLogro'])
            ->where('matricula_id', $matricula->id)
            ->whereHas('aulaAsignaturaDocente', fn ($q) => $q->where('anio_escolar_id', $anioEscolarId))
            ->get()
            ->groupBy('aula_asignatura_docente_id');

        // Mapa de corte numérico -> id
        $cortePorNumero = $cortes->keyBy('numero');

        // Construir la estructura por áreas reales (campo 'area' de la asignatura).
        $areas = [];
        $acumuladoCortes = [1 => [], 2 => [], 3 => [], 4 => []];

        foreach ($asignaciones as $asignacion) {
            $notasAsignatura = $notas->get($asignacion->id, collect());

            $cortesData = [1 => null, 2 => null, 3 => null, 4 => null];
            $finalCuan = null;

            // Mapa id de corte -> número para notas
            $notaPorCorte = [];
            foreach ($notasAsignatura as $nota) {
                $corte = $cortes->firstWhere('id', $nota->corte_evaluativo_id);
                if (!$corte) continue;
                $numero = $corte->numero;
                $notaPorCorte[$numero] = $nota;
            }

            $notasFinales = [];
            foreach ([1, 2, 3, 4] as $numero) {
                $nota = $notaPorCorte[$numero] ?? null;
                if ($nota && !is_null($nota->nota_cuantitativa)) {
                    $cuan = (float) $nota->nota_cuantitativa;
                    $cua = $this->notaService->calcularIndicadorLogro((int) round($cuan));

                    $cortesData[$numero] = ['cua' => $cua, 'cuan' => number_format($cuan, 0)];
                    $acumuladoCortes[$numero][] = $cuan;
                    $notasFinales[$numero] = $cuan;
                }
            }

            // Nota final: promedio de los cortes con nota (si hay los 4, usar promedio simple)
            if (count($notasFinales) === 4) {
                $finalCuan = $this->notaService->calcularNotaFinal(
                    (int) round($notasFinales[1]),
                    (int) round($notasFinales[2]),
                    (int) round($notasFinales[3]),
                    (int) round($notasFinales[4]),
                );
            } elseif (count($notasFinales) > 0) {
                $finalCuan = (int) round(array_sum($notasFinales) / count($notasFinales));
            }

            $final = ($finalCuan !== null)
                ? ['cua' => $this->notaService->calcularIndicadorLogro($finalCuan), 'cuan' => number_format($finalCuan, 0)]
                : null;

            $area = $asignacion->asignatura->area ?? 'Otras Áreas';

            $areas[$area][] = [
                'nombre' => $asignacion->asignatura->nombre,
                'cortes' => $cortesData,
                'final' => $final,
            ];
        }

        // Promedios por corte
        $promedios = [];
        foreach ([1, 2, 3, 4] as $numero) {
            $valores = $acumuladoCortes[$numero];
            if (count($valores) > 0) {
                $promCuan = round(array_sum($valores) / count($valores), 2);
                $promedios[$numero] = [
                    'cua' => $this->notaService->calcularIndicadorLogro((int) round($promCuan)),
                    'cuan' => number_format($promCuan, 0),
                ];
            } else {
                $promedios[$numero] = null;
            }
        }

        // Asistencia por corte (ausencias justificadas/injustificadas por rango de fechas)
        $asistencia = [];
        foreach ([1, 2, 3, 4] as $numero) {
            $corte = $cortePorNumero->get($numero);
            $query = AsistenciaAula::where('matricula_id', $matricula->id);
            if ($corte) {
                $query->whereBetween('fecha', [$corte->fecha_inicio, $corte->fecha_fin]);
            }
            $registros = $query->get();

            $injustificadas = $registros->where('estado_asistencia', 'Ausencia Injustificada')->count();
            $justificadas = $registros->where('estado_asistencia', 'Ausencia Justificada')->count();

            $asistencia[$numero] = [
                'injustificadas' => $injustificadas,
                'justificadas' => $justificadas,
            ];
        }

        // Compromiso de padres: derivado del expediente (acepta_compromiso_cristiano)
        $compromiso = [];
        foreach ([1, 2, 3, 4] as $numero) {
            $compromiso[$numero] = $matricula->alumno->acepta_compromiso_cristiano ? 'MB' : '—';
        }

        return view('academico.boletin.show', compact(
            'matricula', 'areas', 'promedios', 'asistencia', 'compromiso', 'corteActual'
        ));
    }
}