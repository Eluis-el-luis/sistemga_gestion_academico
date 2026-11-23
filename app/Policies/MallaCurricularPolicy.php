<?php

namespace App\Policies;

use App\Models\MallaCurricular;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class MallaCurricularPolicy
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
            return $usuario->hasPermissionTo('malla.ver')
                || $usuario->hasPermissionTo('malla.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'secretaria', 'gestor_it']);
        } catch (\Throwable $e) {
            Log::error('Error en MallaCurricularPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, MallaCurricular $malla): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, ['gestor_it', 'coordinador', 'secretaria'])) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en MallaCurricularPolicy::view', [
                'user_id' => $usuario->id,
                'malla_id' => $malla->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('malla.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, MallaCurricular $malla): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            if ($this->isCoordinador($usuario) && $this->coordinatesModality($usuario, $malla, 'grado.modalidad_id')) {
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en MallaCurricularPolicy::update', [
                'user_id' => $usuario->id,
                'malla_id' => $malla->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, MallaCurricular $malla): bool
    {
        return $this->update($usuario, $malla);
    }
}