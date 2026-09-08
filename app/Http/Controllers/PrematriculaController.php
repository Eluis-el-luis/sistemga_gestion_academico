<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\AulaAsignaturaDocente;
use App\Services\NotaService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PrematriculaController extends Controller
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

        // 1. Obtener el aula activa del docente guía
        $aula = Aula::with(['grado', 'anioEscolar'])
            ->whereHas('anioEscolar', fn ($q) => $q->where('activo', true))
            ->where('docente_guia_id', $usuario->docente->id ?? 0)
            ->first();

        if (!$aula) {
            return redirect()->route('dashboard')->with('error', 'No tienes un aula asignada como docente guía.');
        }

        // 2. Asignaturas que se imparten en el aula
        $asignacionesIds = AulaAsignaturaDocente::where('aula_id', $aula->id)
            ->where('anio_escolar_id', $aula->anio_escolar_id)
            ->pluck('id');

        // 3. Obtener alumnos activos
        $matriculas = Matricula::with('alumno')
            ->where('aula_id', $aula->id)
            ->where('estado', 'activo')
            ->orderBy('id')
            ->get();

        // 4. Lógica de Auditoría de Notas
        foreach ($matriculas as $matricula) {
            $notas = Nota::where('matricula_id', $matricula->id)
                ->whereIn('aula_asignatura_docente_id', $asignacionesIds)
                ->whereNotNull('nota_cuantitativa')
                ->get()
                ->groupBy('aula_asignatura_docente_id');

            $reprobadas = 0;

            foreach ($asignacionesIds as $asignacionId) {
                $notasAsignatura = $notas->get($asignacionId, collect());
                
                $notasCortes = [];
                foreach ($notasAsignatura as $nota) {
                    $notasCortes[] = (float) $nota->nota_cuantitativa;
                }

                // Si tiene notas ingresadas, calculamos el promedio anual hasta la fecha
                if (count($notasCortes) > 0) {
                    $promedioFinal = array_sum($notasCortes) / count($notasCortes);
                    if ($promedioFinal < 60) {
                        $reprobadas++;
                    }
                }
            }

            // Inyectamos el estado al objeto para que la vista decida qué botón mostrar
            $matricula->clases_reprobadas = $reprobadas;
            $matricula->estado_academico = $reprobadas > 0 ? 'En Reparación' : 'Limpio';
        }

        return view('academico.prematricula.index', compact('aula', 'matriculas'));
    }

    public function promover(Request $request, Matricula $matricula)
    {
        // 1. Candado de seguridad
        if ($request->input('estado_academico') === 'En Reparación') {
            return back()->with('error', 'Acción denegada: El estudiante tiene asignaturas reprobadas y debe ser evaluado por Dirección.');
        }

        $aulaActual = $matricula->aula;
        $nombreSeccion = $aulaActual->nombre; // Ej: "A"

        // 2. Localizar el próximo grado (asumiendo que los IDs de grado son secuenciales: 1ero, 2do, 3ero...)
        $proximoGrado = \App\Models\Grado::where('id', '>', $aulaActual->grado_id)->orderBy('id')->first();
        if (!$proximoGrado) {
            return back()->with('error', 'No existe un grado superior configurado en el sistema para este alumno.');
        }

        // 3. Localizar el próximo año escolar (2027)
        $proximoAnio = \App\Models\AnioEscolar::where('id', '>', $matricula->anio_escolar_id)->orderBy('id')->first();
        if (!$proximoAnio) {
            return back()->with('error', 'El próximo ciclo escolar aún no ha sido aperturado en el sistema.');
        }

        // 4. Buscar la coincidencia exacta de Aula (Nuevo Grado + Misma Sección + Nuevo Año)
        $proximaAula = \App\Models\Aula::where('grado_id', $proximoGrado->id)
            ->where('nombre', $nombreSeccion)
            ->where('anio_escolar_id', $proximoAnio->id)
            ->first();

        if (!$proximaAula) {
            return back()->with('error', 'El aula para ' . $proximoGrado->nombre . ' - Sección "' . $nombreSeccion . '" aún no ha sido creada para el próximo año.');
        }

        // 5. Ejecutar la promoción automática
        Matricula::updateOrCreate(
            [
                'alumno_id' => $matricula->alumno_id,
                'anio_escolar_id' => $proximoAnio->id,
            ],
            [
                'aula_id' => $proximaAula->id,
                'estado' => 'activo',
                'fecha_matricula' => now(),
            ]
        );

        return back()->with('success', 'Alumno promovido automáticamente a ' . $proximoGrado->nombre . ' - Sección "' . $nombreSeccion . '".');
    }
    public function remitir(Request $request, Matricula $matricula)
    {
        // Aquí conectaremos con la "sala de espera" del panel de la Directora
        // Puede ser actualizando un campo 'requiere_atencion_direccion' a true en la matrícula

        return back()->with('success', 'El expediente ha sido bloqueado y remitido a Dirección.');
    }
}