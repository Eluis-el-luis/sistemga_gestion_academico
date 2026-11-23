<?php

namespace App\Services;

use App\Models\AsistenciaAula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CitaPsicologiaService
{
    public function programarCitaYJustificar(array $datos, Usuario $psicologa): array
    {
        try {
            return DB::transaction(function () use ($datos, $psicologa) {
                $matricula = Matricula::with(['alumno', 'aula.docenteGuia.usuario', 'aula.asignaciones.docente.usuario', 'aula.asignaciones.asignatura'])
                    ->findOrFail($datos['matricula_id']);

                $fecha = $datos['fecha'];
                $justificar = (bool) ($datos['justificar_clase'] ?? true);

                if ($justificar) {
                    AsistenciaAula::updateOrCreate(
                        [
                            'matricula_id' => $matricula->id,
                            'fecha' => $fecha,
                        ],
                        [
                            'estado_asistencia' => 'Ausencia Justificada',
                        ]
                    );
                }

                $docentesNotificados = collect();
                if ($matricula->aula?->docenteGuia?->usuario) {
                    $docentesNotificados->push([
                        'usuario_id' => $matricula->aula->docenteGuia->usuario->id,
                        'tipo' => 'docente_guia',
                        'aula' => $matricula->aula->nombre,
                    ]);
                }

                $asignaciones = AulaAsignaturaDocente::with(['docente.usuario', 'asignatura'])
                    ->where('aula_id', $matricula->aula_id)
                    ->where('activo', true)
                    ->when(!empty($datos['bloque_horario_id']), function ($q) use ($datos) {
                        $q->whereHas('horarios', fn ($qh) => $qh->where('bloque_horario_id', $datos['bloque_horario_id']));
                    })
                    ->get();

                foreach ($asignaciones as $asignacion) {
                    if ($asignacion->docente?->usuario) {
                        $docentesNotificados->push([
                            'usuario_id' => $asignacion->docente->usuario->id,
                            'tipo' => 'docente_asignatura',
                            'aula' => $matricula->aula?->nombre,
                            'asignatura' => $asignacion->asignatura?->nombre,
                        ]);
                    }
                }

                $notificacionesUnicas = $docentesNotificados->unique('usuario_id')->values()->all();

                Log::info('Cita de psicología programada para justificación académica', [
                    'psicologa_id' => $psicologa->id,
                    'matricula_id' => $matricula->id,
                    'alumno_id' => $matricula->alumno_id,
                    'aula_id' => $matricula->aula_id,
                    'fecha' => $fecha,
                    'hora_inicio' => $datos['hora_inicio'] ?? null,
                    'hora_fin' => $datos['hora_fin'] ?? null,
                    'justificada' => $justificar,
                    'docentes_notificados' => count($notificacionesUnicas),
                ]);

                return [
                    'matricula' => $matricula,
                    'fecha' => $fecha,
                    'justificada' => $justificar,
                    'docentes_notificados' => $notificacionesUnicas,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Error al programar cita de psicología y justificación', [
                'psicologa_id' => $psicologa->id,
                'datos' => $datos,
                'exception' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No fue posible registrar la cita y notificación correspondiente.', 0, $e);
        }
    }
}