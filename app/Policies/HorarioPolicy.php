<?php

namespace App\Policies;

use App\Models\Horario;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class HorarioPolicy
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
        return $usuario->hasPermissionTo('horarios.ver') || $usuario->hasPermissionTo('horarios.gestionar');
    }

    public function view(Usuario $usuario, Horario $horario): bool
    {
        if (!$usuario->hasPermissionTo('horarios.ver') && !$usuario->hasPermissionTo('horarios.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente que imparte la asignatura O docente guía del aula
        return $this->ownsViaAsignacion($usuario, $horario, 'aulaAsignaturaDocente');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('horarios.gestionar');
    }

    public function update(Usuario $usuario, Horario $horario): bool
    {
        if (!$usuario->hasPermissionTo('horarios.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Solo docente de la asignación O docente guía del aula
        return $this->ownsViaAsignacion($usuario, $horario, 'aulaAsignaturaDocente');
    }

    public function delete(Usuario $usuario, Horario $horario): bool
    {
        if (!$usuario->hasPermissionTo('horarios.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        return $this->ownsViaAsignacion($usuario, $horario, 'aulaAsignaturaDocente');
    }
}