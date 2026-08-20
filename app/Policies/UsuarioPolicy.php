<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;

    public function before(Usuario $usuario, $ability)
    {
        // Director y Subdirector tienen poder absoluto
        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            return true;
        }
    }

    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('configuracion.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('configuracion.gestionar');
    }

    public function update(Usuario $usuario, Usuario $modelo): bool
    {
        return $usuario->hasPermissionTo('configuracion.gestionar');
    }
}