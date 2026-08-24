<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;


    public function viewAny(Usuario $authUser): bool
    {
        return $authUser->hasPermissionTo('configuracion.ver');
    }

    public function create(Usuario $authUser): bool
    {
        return $authUser->hasPermissionTo('configuracion.gestionar');
    }

    public function update(Usuario $authUser, Usuario $targetUser): bool
    {
        // Regla: La cuenta de dirección sólo puede ser editada por ella misma.
        if ($targetUser->hasRole('Director')) {
            return $authUser->id === $targetUser->id;
        }

        // Para el resto de usuarios, el Gestor o Subdirector pueden editar si tienen el permiso.
        return $authUser->hasPermissionTo('configuracion.gestionar');
    }

    public function delete(Usuario $authUser, Usuario $targetUser): bool
    {
        // Regla: Solo la Directora puede borrar. Además, no puede borrarse a sí misma.
        return $authUser->hasRole('Director') && $authUser->id !== $targetUser->id;
    }
}