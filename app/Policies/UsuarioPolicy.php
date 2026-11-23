<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class UsuarioPolicy
{
    use HandlesAuthorization, HasResourceOwnership;

    public function viewAny(Usuario $authUser): bool
    {
        try {
            return $this->hasRoleKey($authUser, ['directora', 'subdirectora', 'gestor_it', 'secretaria']);
        } catch (\Throwable $e) {
            Log::error('Error en UsuarioPolicy::viewAny', [
                'auth_user_id' => $authUser->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $authUser): bool
    {
        try {
            return $this->hasRoleKey($authUser, ['directora', 'subdirectora', 'gestor_it', 'secretaria']);
        } catch (\Throwable $e) {
            Log::error('Error en UsuarioPolicy::create', [
                'auth_user_id' => $authUser->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function update(Usuario $authUser, Usuario $targetUser): bool
    {
        try {
            if ($this->isDirectora($targetUser)) {
                return (int) $authUser->id === (int) $targetUser->id;
            }

            if ($this->isSubdirectora($targetUser)) {
                return $this->isSuperAdmin($authUser);
            }

            return $this->hasRoleKey($authUser, ['directora', 'subdirectora', 'gestor_it', 'secretaria']);
        } catch (\Throwable $e) {
            Log::error('Error en UsuarioPolicy::update', [
                'auth_user_id' => $authUser->id,
                'target_user_id' => $targetUser->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $authUser, Usuario $targetUser): bool
    {
        try {
            if ((int) $authUser->id === (int) $targetUser->id) {
                return false;
            }

            if ($this->isDirectora($targetUser)) {
                return false;
            }

            if ($this->isSubdirectora($targetUser)) {
                return $this->isDirectora($authUser);
            }

            return $this->hasRoleKey($authUser, ['directora', 'subdirectora', 'gestor_it']);
        } catch (\Throwable $e) {
            Log::error('Error en UsuarioPolicy::delete', [
                'auth_user_id' => $authUser->id,
                'target_user_id' => $targetUser->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}