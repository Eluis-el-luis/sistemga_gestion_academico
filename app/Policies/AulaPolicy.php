<?php

namespace App\Policies;

use App\Models\Aula;
use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AulaPolicy
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
            return $usuario->hasPermissionTo('aulas.ver')
                || $usuario->hasPermissionTo('aulas.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'psicologa', 'gestor_it']);
        } catch (\Throwable $e) {
            Log::error('Error en AulaPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Aula $aula): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, ['gestor_it', 'secretaria', 'psicologa'])) {
                return true;
            }

            if ($this->isCoordinador($usuario)) {
                return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
            }

            if ($this->ownsAula($usuario, $aula)) {
                return true;
            }

            $docente = $usuario->docente;
            if ($docente) {
                return AulaAsignaturaDocente::where('aula_id', $aula->id)
                    ->where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->exists();
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en AulaPolicy::view', [
                'user_id' => $usuario->id,
                'aula_id' => $aula->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('aulas.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, Aula $aula): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en AulaPolicy::update', [
                'user_id' => $usuario->id,
                'aula_id' => $aula->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Aula $aula): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en AulaPolicy::delete', [
                'user_id' => $usuario->id,
                'aula_id' => $aula->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}