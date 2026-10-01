<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;

    public function viewAny(Usuario $authUser): bool
    {
        return $authUser->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios']);
    }

    public function create(Usuario $authUser): bool
    {
        return $authUser->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios']);
    }

    public function update(Usuario $authUser, Usuario $targetUser): bool
    {
        // 1. BLINDAJE DE DIRECCIÓN: Su cuenta solo puede ser editada por ella misma.
        if ($targetUser->hasRole('Director')) {
            return $authUser->id === $targetUser->id;
        }

        // 2. BLINDAJE DE SUBDIRECCIÓN: El Gestor de Usuarios NO puede editarla.
        if ($targetUser->hasRole('Subdirector')) {
            return $authUser->hasAnyRole(['Director', 'Subdirector']);
        }

        // 3. REGLA GENERAL: Para el resto del personal, los tres roles tienen acceso.
        return $authUser->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios']);
    }

    public function delete(Usuario $authUser, Usuario $targetUser): bool
    {
        // 1. PREVENCIÓN: Nadie puede bloquearse/eliminarse a sí mismo por error.
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // 2. BLINDAJE DE DIRECCIÓN: Totalmente intocable.
        if ($targetUser->hasRole('Director')) {
            return false;
        }

        // 3. BLINDAJE DE SUBDIRECCIÓN: Solo la Dirección puede bloquear/eliminar esta cuenta.
        if ($targetUser->hasRole('Subdirector')) {
            return $authUser->hasRole('Director');
        }

        // 4. REGLA GENERAL: Para el resto del personal, los tres roles pueden bloquear (incluyendo al Gestor).
        return $authUser->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios']);
    }
}