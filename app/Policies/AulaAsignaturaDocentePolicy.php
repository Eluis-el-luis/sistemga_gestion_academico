<?php

namespace App\Policies;

use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AulaAsignaturaDocentePolicy
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
            return $usuario->hasPermissionTo('asignaturas_aula.ver')
                || $usuario->hasPermissionTo('asignaturas_aula.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'gestor_it']);
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            if ($this->isCoordinador($usuario)) {
                return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
            }

            if ($this->ownsAula($usuario, $asignacion, 'aula')) {
                return true;
            }

            return $this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion);
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::view', [
                'user_id' => $usuario->id,
                'asignacion_id' => $asignacion->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asignaturas_aula.gestionar')
            || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id')) {
                return true;
            }

            return $this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion);
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::update', [
                'user_id' => $usuario->id,
                'asignacion_id' => $asignacion->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::delete', [
                'user_id' => $usuario->id,
                'asignacion_id' => $asignacion->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function calificar(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) {
                return true;
            }

            return $this->ownsAula($usuario, $asignacion, 'aula');
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::calificar', [
                'user_id' => $usuario->id,
                'asignacion_id' => $asignacion->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function gestionarAsistencia(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) {
                return true;
            }

            return $this->ownsAula($usuario, $asignacion, 'aula');
        } catch (\Throwable $e) {
            Log::error('Error en AulaAsignaturaDocentePolicy::gestionarAsistencia', [
                'user_id' => $usuario->id,
                'asignacion_id' => $asignacion->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function ver(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        return $this->view($usuario, $asignacion);
    }
}