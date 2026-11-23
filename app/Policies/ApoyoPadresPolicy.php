<?php

namespace App\Policies;

use App\Models\ApoyoPadres;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class ApoyoPadresPolicy
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
            return $usuario->hasPermissionTo('apoyo_padres.ver')
                || $usuario->hasPermissionTo('apoyo_padres.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'docente_guia', 'psicologa']);
        } catch (\Throwable $e) {
            Log::error('Error en ApoyoPadresPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, 'psicologa')) {
                return true;
            }

            if ($this->ownsAula($usuario, $apoyo)) {
                return true;
            }

            return $this->coordinatesModality($usuario, $apoyo, 'aula.modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en ApoyoPadresPolicy::view', [
                'user_id' => $usuario->id,
                'apoyo_id' => $apoyo->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('apoyo_padres.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'docente_guia']);
    }

    public function update(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isSecretaria($usuario)) {
                return true;
            }

            if ($this->ownsAula($usuario, $apoyo)) {
                return true;
            }

            return $this->coordinatesModality($usuario, $apoyo, 'aula.modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en ApoyoPadresPolicy::update', [
                'user_id' => $usuario->id,
                'apoyo_id' => $apoyo->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        return $this->update($usuario, $apoyo);
    }
}