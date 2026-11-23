<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class NotaPolicy
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
            return $usuario->hasPermissionTo('notas.ver')
                || $usuario->hasPermissionTo('notas.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente']);
        } catch (\Throwable $e) {
            Log::error('Error en NotaPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Nota $nota): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $nota, 'aulaAsignaturaDocente.aula.modalidad_id')) {
                return true;
            }

            return $this->checkOwnership($usuario, $nota);
        } catch (\Throwable $e) {
            Log::error('Error en NotaPolicy::view', [
                'user_id' => $usuario->id,
                'nota_id' => $nota->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'docente_guia', 'docente_asignatura', 'docente_suplente']);
    }

    public function update(Usuario $usuario, Nota $nota): bool
    {
        try {
            if ($this->isCorteBloqueado($nota, 'aulaAsignaturaDocente', 'corteEvaluativo')) {
                return false;
            }

            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            return $this->checkOwnership($usuario, $nota);
        } catch (\Throwable $e) {
            Log::error('Error en NotaPolicy::update', [
                'user_id' => $usuario->id,
                'nota_id' => $nota->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Nota $nota): bool
    {
        try {
            if (!$this->isSuperAdmin($usuario)) {
                return false;
            }

            return !$this->isCorteBloqueado($nota, 'aulaAsignaturaDocente', 'corteEvaluativo');
        } catch (\Throwable $e) {
            Log::error('Error en NotaPolicy::delete', [
                'user_id' => $usuario->id,
                'nota_id' => $nota->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function checkOwnership(Usuario $usuario, Nota $nota): bool
    {
        return $this->ownsViaAsignacion($usuario, $nota, 'aulaAsignaturaDocente');
    }
}