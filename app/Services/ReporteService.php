<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\AsistenciaAula;
use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Modalidad;
use App\Models\Nota;

class ReporteService
{
    protected NotaService $notaService;

    public function __construct(NotaService $notaService)
    {
        $this->notaService = $notaService;
    }

    /**
     * Obtiene el año escolar activo (o null si no existe).
     */
    public function anioActivo(): ?AnioEscolar
    {
        return AnioEscolar::where('activo', true)->first();
    }

    /**
     * AnioEscolar a filtrar según el request; si no se envía, usa el activo.
     */
    public function resolverAnio(?int $anioId): ?AnioEscolar
    {
        if ($anioId) {
            return AnioEscolar::find($anioId);
        }

        return $this->anioActivo() ?? AnioEscolar::orderBy('id', 'desc')->first();
    }

    /**
     * Control de Ingreso de Notas: por cada asignación (aula-asignatura-docente),
     * cuántas matriculas activas tienen nota registrada vs. el total (pendientes).
     */
    public function ingresoNotas(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);
        if (!$anio) {
            return [];
        }

        $asignaciones = AulaAsignaturaDocente::with(['aula.grado', 'asignatura', 'docente.usuario'])
            ->where('anio_escolar_id', $anio->id)
            ->orderBy('aula_id')
            ->get();

        $filas = [];

        foreach ($asignaciones as $asignacion) {
            $total = Matricula::where('aula_id', $asignacion->aula_id)
                ->where('estado', 'activo')
                ->count();

            $conNota = Nota::where('aula_asignatura_docente_id', $asignacion->id)
                ->distinct('matricula_id')
                ->count('matricula_id');

            $pendientes = max(0, $total - $conNota);
            $porcentaje = $total > 0 ? round(($conNota / $total) * 100, 1) : 0;

            $docenteNombre = $asignacion->docente->usuario->nombre_completo ?? 'Sin asignar';

            $filas[] = [
                'aula' => $asignacion->aula->nombre,
                'grado' => $asignacion->aula->grado->nombre,
                'asignatura' => $asignacion->asignatura->nombre,
                'docente' => $docenteNombre,
                'total' => $total,
                'registradas' => $conNota,
                'pendientes' => $pendientes,
                'porcentaje' => $porcentaje,
            ];
        }

        return $filas;
    }

    /**
     * Resumen global del ingreso de notas (para la tarjeta del hub).
     */
    public function resumenIngresoNotas(?int $anioId): array
    {
        $filas = $this->ingresoNotas($anioId);

        $totalAlumnos = array_sum(array_column($filas, 'total'));
        $totalRegistradas = array_sum(array_column($filas, 'registradas'));
        $totalPendientes = array_sum(array_column($filas, 'pendientes'));

        return [
            'asignaciones' => count($filas),
            'total_notas_esperadas' => $totalAlumnos,
            'notas_registradas' => $totalRegistradas,
            'notas_pendientes' => $totalPendientes,
            'porcentaje' => $totalAlumnos > 0 ? round(($totalRegistradas / $totalAlumnos) * 100, 1) : 0,
        ];
    }

    /**
     * Asistencia por turno y grado/sección para una fecha dada (default: hoy).
     */
    public function asistencia(?string $fecha, ?int $anioId, ?int $modalidadId): array
    {
        $anio = $this->resolverAnio($anioId);
        $fecha = $fecha ?: now()->toDateString();

        $query = Aula::with(['grado.modalidad', 'modalidad', 'anioEscolar'])
            ->where('anio_escolar_id', $anio?->id);

        if ($modalidadId) {
            $query->where('modalidad_id', $modalidadId);
        }

        $aulas = $query->orderBy('turno')->orderBy('grado_id')->get();

        $reporte = [];

        foreach ($aulas as $aula) {
            $matriculas = Matricula::where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->get();

            $matriculaIds = $matriculas->pluck('id');

            $asistencias = AsistenciaAula::whereIn('matricula_id', $matriculaIds)
                ->where('fecha', $fecha)
                ->get()
                ->keyBy('matricula_id');

            $presentes = 0;
            $ausentes = 0;
            $justificadas = 0;

            foreach ($matriculas as $matricula) {
                $estado = $asistencias->get($matricula->id)?->estado_asistencia;

                if (is_null($estado)) {
                    $ausentes++; // sin registro se considera ausente para reportes
                } elseif ($estado === 'Presente' || $estado === 'Actividad Institucional') {
                    $presentes++;
                } elseif ($estado === 'Ausencia Justificada') {
                    $justificadas++;
                } else {
                    $ausentes++;
                }
            }

            $total = $matriculas->count();
            $reporte[] = [
                'aula' => $aula->nombre,
                'turno' => $aula->turno,
                'grado' => $aula->grado->nombre,
                'modalidad' => $aula->modalidad->nombre,
                'total' => $total,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
                'justificadas' => $justificadas,
                'porcentaje_asistencia' => $total > 0 ? round(($presentes / $total) * 100, 1) : 0,
            ];
        }

        return $reporte;
    }

    /**
     * Rendimiento académico por sección: promedio y aprobados por aula.
     */
    public function rendimiento(?int $anioId, ?int $modalidadId): array
    {
        $anio = $this->resolverAnio($anioId);

        $query = Aula::with(['grado.modalidad', 'modalidad'])
            ->where('anio_escolar_id', $anio?->id);

        if ($modalidadId) {
            $query->where('modalidad_id', $modalidadId);
        }

        $aulas = $query->orderBy('grado_id')->get();

        $reporte = [];

        foreach ($aulas as $aula) {
            $matriculas = Matricula::with('notas')
                ->where('aula_id', $aula->id)
                ->where('estado', 'activo')
                ->get();

            $promedios = [];
            $aprobados = 0;
            $reprobados = 0;

            foreach ($matriculas as $matricula) {
                $promAlumno = (float) $matricula->notas->avg('nota_cuantitativa');
                if ($matricula->notas->count() > 0) {
                    $promedios[] = $promAlumno;
                    if ($promAlumno >= 60) {
                        $aprobados++;
                    } else {
                        $reprobados++;
                    }
                }
            }

            $total = count($promedios);
            $reporte[] = [
                'aula' => $aula->nombre,
                'grado' => $aula->grado->nombre,
                'modalidad' => $aula->modalidad->nombre,
                'total_evaluados' => $total,
                'promedio' => $total > 0 ? round(array_sum($promedios) / $total, 2) : 0,
                'aprobados' => $aprobados,
                'reprobados' => $reprobados,
                'porcentaje_aprobacion' => $total > 0 ? round(($aprobados / $total) * 100, 1) : 0,
            ];
        }

        return $reporte;
    }

    /**
     * Reporte estadístico MINED: matriculados por modalidad (resumen general).
     */
    public function mined(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);

        $modalidades = Modalidad::all();
        $datos = [];

        foreach ($modalidades as $modalidad) {
            $matriculados = Matricula::where('anio_escolar_id', $anio?->id)
                ->whereHas('aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->where('estado', 'activo')
                ->count();

            $retirados = Matricula::where('anio_escolar_id', $anio?->id)
                ->whereHas('aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->where('estado', 'retirado')
                ->count();

            $promedio = 0;
            $notas = Nota::whereHas('matricula.aula', fn ($q) => $q->where('modalidad_id', $modalidad->id))
                ->whereHas('matricula', fn ($q) => $q->where('anio_escolar_id', $anio?->id))
                ->avg('nota_cuantitativa');

            $datos[] = [
                'modalidad' => $modalidad->nombre,
                'matriculados' => $matriculados,
                'retirados' => $retirados,
                'promedio' => $promedio ? round((float) $promedio, 2) : 0,
                'retencion' => ($matriculados + $retirados) > 0
                    ? round(($matriculados / ($matriculados + $retirados)) * 100, 1)
                    : 0,
            ];
        }

        return $datos;
    }

    /**
     * Población estudiantil: matrícula, retiros y expedientes incompletos.
     */
    public function estudiantes(?int $anioId): array
    {
        $anio = $this->resolverAnio($anioId);
        $anioId = $anio?->id;

        $matriculados = Matricula::where('anio_escolar_id', $anioId)->where('estado', 'activo')->count();
        $retirados = Matricula::where('anio_escolar_id', $anioId)->where('estado', 'retirado')->count();

        // Expedientes incompletos: alumnos activos con campos obligatorios vacíos
        $incompletos = Alumno::where(function ($q) {
            $q->whereNull('direccion_domiciliar')
              ->orWhereNull('madre_nombre_completo')
              ->orWhereNull('madre_telefono')
              ->orWhereNull('tutor_nombre_completo')
              ->orWhereNull('fecha_nacimiento');
        })->count();

        $totalAlumnos = Alumno::count();

        return [
            'total_alumnos' => $totalAlumnos,
            'matriculados' => $matriculados,
            'retirados' => $retirados,
            'expedientes_incompletos' => $incompletos,
        ];
    }

    /**
     * Responsables y Padres: adopción digital (si tiene teléfono/email registrado).
     */
    public function padres(): array
    {
        $alumnos = Alumno::all();

        $conTelefono = $alumnos->filter(fn ($a) => !empty($a->madre_telefono) || !empty($a->padre_telefono) || !empty($a->tutor_telefono))->count();
        $conEmail = $alumnos->filter(function ($a) {
            return $a->usuario && !empty($a->usuario->email);
        })->count();

        $total = $alumnos->count();

        return [
            'total' => $total,
            'con_telefono' => $conTelefono,
            'con_email' => $conEmail,
            'porcentaje_adopcion' => $total > 0 ? round(($conTelefono / $total) * 100, 1) : 0,
        ];
    }
}