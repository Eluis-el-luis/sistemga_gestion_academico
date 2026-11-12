<?php

namespace App\Policies;

use App\Models\Boletin;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class BoletinPolicy
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
        return $usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar');
    }

    public function view(Usuario $usuario, Boletin $boletin): bool
    {
        if (!$usuario->hasPermissionTo('boletines.ver') && !$usuario->hasPermissionTo('boletines.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula de la matrícula
        return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('boletines.gestionar');
    }

    public function update(Usuario $usuario, Boletin $boletin): bool
    {
        if (!$usuario->hasPermissionTo('boletines.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía Y Coordinador/Secretaria con permiso
        return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula')
            || $this->coordinatesModality($usuario, $boletin, 'matricula.aula.modalidad_id');
    }

    public function delete(Usuario $usuario, Boletin $boletin): bool
    {
        return $this->isSuperAdmin($usuario);
    }
}