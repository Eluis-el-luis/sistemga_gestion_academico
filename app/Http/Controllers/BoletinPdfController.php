<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\CorteEvaluativo;
use App\Models\AulaAsignaturaDocente;
use App\Models\Nota;
use App\Models\AsistenciaAula;
use App\Models\EvaluacionFamiliar;
use App\Models\GrupoMateria;
use App\Services\NotaService;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;

class BoletinPdfController extends Controller
{
    use AuthorizesRequests;

    protected $notaService;
    protected $reporteService;

    public function __construct(NotaService $notaService, ReporteService $reporteService)
    {
        $this->notaService = $notaService;
        $this->reporteService = $reporteService;
    }

    /**
     * Generar PDF del boletín de un alumno para un corte específico.
     */
    public function boletin(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('view', $matricula);

            $data = $this->prepareBoletinData($matricula, $request->query('corte_evaluativo_id'));
            $data['logo'] = $this->logoDataUri();

            $pdf = Pdf::loadView('pdf.boletin', $data)
                ->setPaper('letter', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                ]);

            $filename = "boletin_{$matricula->alumno->codigo_unico_persona}_corte{$data['corteActual']->numero}_{$data['matricula']->anioEscolar->nombre}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA
            return back()->with('error', 'No se pudo generar el boletín en PDF. Verifica los datos e intenta de nuevo.');
        }
    }

    /**
     * Generar PDF de constancia de notas (historial académico).
     */
    public function constancia(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('view', $matricula);

            $data = $this->prepareConstanciaData($matricula);

            $pdf = Pdf::loadView('pdf.constancia', $data)
                ->setPaper('letter', 'landscape')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                ]);

            $filename = "constancia_notas_{$matricula->alumno->codigo_unico_persona}_{$data['matricula']->anioEscolar->nombre}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA
            return back()->with('error', 'No se pudo generar la constancia de notas en PDF. Verifica los datos e intenta de nuevo.');
        }
    }

    /**
     * Generar PDF de certificado de notas (formato oficial MINED).
     */
    public function certificado(Request $request, Matricula $matricula)
    {
        try {
            $this->authorize('view', $matricula);

            $data = $this->reporteService->historialPorEstudiante($matricula->alumno_id);

            $pdf = Pdf::loadView('pdf.certificado_notas', [
                'matricula' => $data['matricula'],
                'alumno' => $data['alumno'],
                'resumenAsignaturas' => $data['resumenAsignaturas'],
                'promedioGeneral' => $data['promedioGeneral'],
            ])
                ->setPaper('letter', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                ]);

            $filename = "certificado_notas_{$matricula->alumno->codigo_unico_persona}_{$data['matricula']->anioEscolar->nombre}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA
            return back()->with('error', 'No se pudo generar el certificado de notas en PDF. Verifica los datos e intenta de nuevo.');
        }
    }

    /**
     * Generar PDFs masivos para un aula completa (ZIP).
     */
    public function masivo(Request $request)
    {
        // Validación AFUERA del try-catch
        $request->validate([
            'aula_id' => 'required|exists:aula,id',
            'corte_evaluativo_id' => 'nullable|exists:corte_evaluativo,id',
            'tipo' => 'required|in:boletin,constancia,certificado',
        ]);

        try {
            // La generación masiva puede tardar: liberamos el bloqueo de sesión
            // para que el resto de la aplicación siga respondiendo a este usuario,
            // y ampliamos los límites de ejecución del proceso.
            session()->save();
            @set_time_limit(0);
            @ini_set('memory_limit', '512M');

            $aula = \App\Models\Aula::with(['grado', 'anioEscolar'])->findOrFail($request->aula_id);
            $corteId = $request->corte_evaluativo_id;

            $matriculas = Matricula::with('alumno')
                ->where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->orderBy('id')
                ->get();

            if ($matriculas->isEmpty()) {
                return back()->with('error', 'No hay estudiantes matriculados en esta sección.');
            }

            $zip = new \ZipArchive();
            $zipName = "{$request->tipo}s_{$aula->nombre}_{$aula->grado->nombre}_{$aula->anioEscolar->nombre}.zip";
            $tempPath = storage_path("app/temp/{$zipName}");

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'No se pudo crear el archivo ZIP.');
            }

            foreach ($matriculas as $matricula) {
                try {
                    $pdfContent = $this->generatePdfContent($matricula, $request->tipo, $corteId);
                    $filename = $this->getPdfFilename($matricula, $request->tipo, $corteId);
                    $zip->addFromString($filename, $pdfContent);
                } catch (\Exception $e) {
                    if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                        throw $e;
                    }
                    \Log::error("Error generando PDF para {$matricula->alumno->nombre_completo}: " . $e->getMessage());
                }
            }

            $zip->close();

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA
            return back()->with('error', 'Ocurrió un error al generar los PDFs masivos. Verifica la sección e intenta de nuevo.');
        }
    }

    /**
     * Preparar datos para el boletín (reutiliza lógica de BoletinController).
     */
    protected function prepareBoletinData(Matricula $matricula, ?int $corteId = null): array
    {
        $matricula->load([
            'alumno',
            'aula.grado',
            'aula.modalidad',
            'aula.docenteGuia.usuario',
            'anioEscolar',
        ]);

        $anioEscolarId = $matricula->anio_escolar_id;
        $cortes = CorteEvaluativo::where('anio_escolar_id', $anioEscolarId)
            ->orderBy('numero')
            ->get();

        $hoy = now()->timezone('America/Managua')->toDateString();

        $corteActual = $corteId
            ? $cortes->firstWhere('id', $corteId)
            : ($cortes->first(fn($c) => $c->fecha_inicio <= $hoy && $c->fecha_fin >= $hoy) ?? $cortes->last());

        $numeroActual = $corteActual->numero ?? 1;

        $asignaciones = AulaAsignaturaDocente::with('asignatura.grupoMateria')
            ->where('aula_id', $matricula->aula_id)
            ->where('anio_escolar_id', $anioEscolarId)
            ->get();

        $cortePorNumero = $cortes->keyBy('numero');
        $areas = [];
        $acumuladoCortes = [1 => [], 2 => [], 3 => [], 4 => []];

        foreach ($asignaciones as $asignacion) {
            $resumen = $this->notaService->calcularResumenAsignatura($matricula, $asignacion);

            $cortesData = [1 => null, 2 => null, 3 => null, 4 => null];
            $notasFinales = [];
            foreach ([1, 2, 3, 4] as $numero) {
                if (isset($resumen['cortes'][$numero]) && $resumen['cortes'][$numero] !== null) {
                    $cuan = (float) $resumen['cortes'][$numero];
                    $cua = $this->notaService->calcularIndicadorLogro((int) round($cuan));

                    $cortesData[$numero] = ['cua' => $cua, 'cuan' => number_format($cuan, 0)];
                    $acumuladoCortes[$numero][] = $cuan;
                    $notasFinales[$numero] = $cuan;
                }
            }

            $finalCuan = $resumen['nota_final'];
            $final = ($finalCuan !== null)
                ? ['cua' => $resumen['indicador_final'], 'cuan' => number_format($finalCuan, 0)]
                : null;

            $area = $asignacion->asignatura->grupoMateria->nombre ?? 'Otros';

            $areas[$area][] = [
                'nombre' => $asignacion->asignatura->nombre,
                'cortes' => $cortesData,
                'final' => $final,
                'aprobado' => $resumen['aprobado'],
            ];
        }

        $ordenGrupos = GrupoMateria::pluck('orden', 'nombre')->toArray();
        uksort($areas, function ($a, $b) use ($ordenGrupos) {
            $oa = $ordenGrupos[$a] ?? 999;
            $ob = $ordenGrupos[$b] ?? 999;
            return $oa <=> $ob;
        });

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

        $asistencia = [];
        foreach ([1, 2, 3, 4] as $numero) {
            if ($numero <= $numeroActual) {
                $corte = $cortePorNumero->get($numero);
                $query = AsistenciaAula::where('matricula_id', $matricula->id);
                if ($corte) {
                    $query->whereBetween('fecha', [$corte->fecha_inicio, $corte->fecha_fin]);
                }
                $registros = $query->get();

                $asistencia[$numero] = [
                    'injustificadas' => $registros->where('estado_asistencia', 'Ausencia Injustificada')->count(),
                    'justificadas' => $registros->where('estado_asistencia', 'Ausencia Justificada')->count(),
                ];
            } else {
                $asistencia[$numero] = [
                    'injustificadas' => '',
                    'justificadas' => '',
                ];
            }
        }

        $compromiso = [];
        $evaluacionesReales = EvaluacionFamiliar::where('matricula_id', $matricula->id)->get();
        foreach ([1, 2, 3, 4] as $numero) {
            if ($numero <= $numeroActual) {
                $corte = $cortePorNumero->get($numero);
                $eval = $corte ? $evaluacionesReales->firstWhere('corte_evaluativo_id', $corte->id) : null;
                $compromiso[$numero] = $eval ? $eval->evaluacion : '—';
            } else {
                $compromiso[$numero] = '';
            }
        }

        return [
            'matricula' => $matricula,
            'areas' => $areas,
            'promedios' => $promedios,
            'asistencia' => $asistencia,
            'compromiso' => $compromiso,
            'corteActual' => $corteActual,
            'numeroCorteActual' => $numeroActual,
            'cortes' => $cortes,
        ];
    }

    /**
     * Preparar datos para constancia de notas.
     */
    protected function prepareConstanciaData(Matricula $matricula): array
    {
        $matricula->load(['alumno', 'aula.grado', 'aula.modalidad', 'aula.docenteGuia.usuario', 'anioEscolar']);

        $cortes = CorteEvaluativo::where('anio_escolar_id', $matricula->anio_escolar_id)
            ->orderBy('numero')
            ->get();

        $asignaciones = AulaAsignaturaDocente::with('asignatura.grupoMateria')
            ->where('aula_id', $matricula->aula_id)
            ->where('anio_escolar_id', $matricula->anio_escolar_id)
            ->get();

        $filas = [];
        $promediosFinales = [];
        foreach ($asignaciones as $asignacion) {
            $resumen = $this->notaService->calcularResumenAsignatura($matricula, $asignacion);

            $cortesValores = [];
            foreach ($cortes as $corte) {
                $cortesValores[$corte->numero] = $resumen['cortes'][$corte->numero] ?? null;
            }

            $filas[] = [
                'asignatura' => $asignacion->asignatura->nombre,
                'area' => $asignacion->asignatura->grupoMateria->nombre ?? 'Otros',
                'cortes' => $cortesValores,
                'nota_final' => $resumen['nota_final'],
                'indicador' => $resumen['indicador_final'],
                'aprobado' => $resumen['aprobado'],
            ];

            if ($resumen['nota_final'] !== null) {
                $promediosFinales[] = $resumen['nota_final'];
            }
        }

        $promedioGeneral = count($promediosFinales) > 0
            ? round(array_sum($promediosFinales) / count($promediosFinales), 2)
            : null;

        return [
            'matricula' => $matricula,
            'cortes' => $cortes,
            'filas' => $filas,
            'promedioGeneral' => $promedioGeneral,
        ];
    }

    /**
     * Generar contenido PDF en memoria.
     */
    protected function generatePdfContent(Matricula $matricula, string $tipo, ?int $corteId = null): string
    {
        switch ($tipo) {
            case 'boletin':
                $data = $this->prepareBoletinData($matricula, $corteId);
                $data['logo'] = $this->logoDataUri();
                $pdf = Pdf::loadView('pdf.boletin', $data)
                    ->setPaper('letter', 'landscape')
                    ->setOptions(['defaultFont' => 'DejaVu Sans']);
                return $pdf->output();

            case 'constancia':
                $data = $this->prepareConstanciaData($matricula);
                $pdf = Pdf::loadView('pdf.constancia', $data)
                    ->setPaper('letter', 'landscape')
                    ->setOptions(['defaultFont' => 'DejaVu Sans']);
                return $pdf->output();

            case 'certificado':
                $data = $this->reporteService->historialPorEstudiante($matricula->alumno_id);
                $pdf = Pdf::loadView('pdf.certificado_notas', [
                    'matricula' => $data['matricula'],
                    'alumno' => $data['alumno'],
                    'resumenAsignaturas' => $data['resumenAsignaturas'],
                    'promedioGeneral' => $data['promedioGeneral'],
                ])
                    ->setPaper('letter', 'portrait')
                    ->setOptions(['defaultFont' => 'DejaVu Sans']);
                return $pdf->output();

            default:
                throw new \InvalidArgumentException("Tipo de PDF no soportado: {$tipo}");
        }
    }

    /**
     * Logo del colegio embebido como data URI (base64).
     *
     * DomPDF cargaba asset('img/logo.png') mediante HTTP contra el MISMO
     * servidor de desarrollo (php artisan serve atiende una petición a la
     * vez), provocando un bloqueo mutuo: la petición del PDF quedaba colgada
     * esperando la imagen, y al mantener bloqueada la sesión, toda la
     * aplicación dejaba de responder para ese usuario. Embeber la imagen en
     * el HTML evita por completo la petición HTTP interna.
     */
    protected function logoDataUri(): string
    {
        $path = public_path('img/logo.png');

        if (is_file($path)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }

        return '';
    }

    protected function getPdfFilename(Matricula $matricula, string $tipo, ?int $corteId = null): string
    {
        $cup = $matricula->alumno->codigo_unico_persona;
        $anio = $matricula->anioEscolar->nombre;

        if ($tipo === 'boletin' && $corteId) {
            $corte = CorteEvaluativo::find($corteId);
            return "boletin_{$cup}_corte{$corte->numero}_{$anio}.pdf";
        }

        return "{$tipo}_{$cup}_{$anio}.pdf";
    }
}