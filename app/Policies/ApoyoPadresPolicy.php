<?php

namespace App\Policies;

use App\Models\ApoyoPadres;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class ApoyoPadresPolicy
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
        return $usuario->hasPermissionTo('apoyo_padres.ver') || $usuario->hasPermissionTo('apoyo_padres.gestionar');
    }

    public function view(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        if (!$usuario->hasPermissionTo('apoyo_padres.ver') && !$usuario->hasPermissionTo('apoyo_padres.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsAula($usuario, $apoyo)) {
            return true;
        }

        // Coordinador de la modalidad
        if ($this->coordinatesModality($usuario, $apoyo, 'aula.modalidad_id')) {
            return true;
        }

        return false;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('apoyo_padres.gestionar');
    }

    public function update(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        if (!$usuario->hasPermissionTo('apoyo_padres.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsAula($usuario, $apoyo)) {
            return true;
        }

        // Coordinador de la modalidad
        if ($this->coordinatesModality($usuario, $apoyo, 'aula.modalidad_id')) {
            return true;
        }

        // Secretaria
        if ($usuario->hasRole('Secretaria')) {
            return true;
        }

        return false;
    }

    public function delete(Usuario $usuario, ApoyoPadres $apoyo): bool
    {
        return $this->update($usuario, $apoyo);
    }
}