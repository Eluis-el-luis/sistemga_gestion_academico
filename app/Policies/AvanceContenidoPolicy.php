<?php

namespace App\Policies;

use App\Models\AvanceContenido;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class AvanceContenidoPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view']) && $this->isSuperAdmin($usuario)) {
            return true;
        }
        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('avance.ver') || $usuario->hasPermissionTo('avance.gestionar');
    }

    public function view(Usuario $usuario, AvanceContenido $avance): bool
    {
        if (!$usuario->hasPermissionTo('avance.ver') && !$usuario->hasPermissionTo('avance.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente que imparte la asignatura O docente guía del aula
        return $this->ownsViaAsignacion($usuario, $avance, 'aulaAsignaturaDocente');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('avance.gestionar');
    }

    public function update(Usuario $usuario, AvanceContenido $avance): bool
    {
        if (!$usuario->hasPermissionTo('avance.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        return $this->ownsViaAsignacion($usuario, $avance, 'aulaAsignaturaDocente');
    }

    public function delete(Usuario $usuario, AvanceContenido $avance): bool
    {
        return $this->update($usuario, $avance);
    }
}