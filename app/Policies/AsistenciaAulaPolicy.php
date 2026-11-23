<?php

namespace App\Policies;

use App\Models\AsistenciaAula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AsistenciaAulaPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, string $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view'], true) && $this->isSuperAdmin($usuario)) {
            return true;
        }

        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        try {
            return $usuario->hasPermissionTo('asistencia.ver')
                || $usuario->hasPermissionTo('asistencia.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'psicologa']);
        } catch (\Throwable $e) {
            Log::error('Error en AsistenciaAulaPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isSecretaria($usuario) || $this->hasRoleKey($usuario, 'psicologa')) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $asistencia, 'matricula.aula.modalidad_id')) {
                return true;
            }

            if ($this->ownsViaMatriculaAula($usuario, $asistencia, 'matricula')) {
                return true;
            }

            $docente = $usuario->docente;
            if ($docente && $asistencia->matricula?->aula) {
                return AulaAsignaturaDocente::where('aula_id', $asistencia->matricula->aula_id)
                    ->where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->exists();
            }

            return $this->ownsAsAlumno($usuario, $asistencia, 'matricula.alumno.usuario_id');
        } catch (\Throwable $e) {
            Log::error('Error en AsistenciaAulaPolicy::view', [
                'user_id' => $usuario->id,
                'asistencia_id' => $asistencia->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asistencia.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente']);
    }

    public function update(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->ownsViaMatriculaAula($usuario, $asistencia, 'matricula')) {
                return true;
            }

            $docente = $usuario->docente;
            if ($docente && $asistencia->matricula?->aula) {
                return AulaAsignaturaDocente::where('aula_id', $asistencia->matricula->aula_id)
                    ->where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->exists();
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en AsistenciaAulaPolicy::update', [
                'user_id' => $usuario->id,
                'asistencia_id' => $asistencia->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        return $this->update($usuario, $asistencia);
    }
}