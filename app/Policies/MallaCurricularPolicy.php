<?php

namespace App\Policies;

use App\Models\MallaCurricular;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class MallaCurricularPolicy
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
        return $usuario->hasPermissionTo('malla.ver') || $usuario->hasPermissionTo('malla.gestionar');
    }

    public function view(Usuario $usuario, MallaCurricular $malla): bool
    {
        if (!$usuario->hasPermissionTo('malla.ver') && !$usuario->hasPermissionTo('malla.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Gestor de Usuarios, Coordinador, Secretaria ven todo
        if ($usuario->hasAnyRole(['Gestor de Usuarios', 'Coordinador', 'Secretaria'])) {
            return true;
        }

        return false;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('malla.gestionar');
    }

    public function update(Usuario $usuario, MallaCurricular $malla): bool
    {
        if (!$usuario->hasPermissionTo('malla.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Coordinador puede actualizar malla de su modalidad
        // Nota: MallaCurricular no tiene modalidad_id directamente, 
        // se vincula via grado->modalidad_id
        if ($usuario->hasRole('Coordinador') && $this->coordinatesModality($usuario, $malla, 'grado.modalidad_id')) {
            return true;
        }

        return false;
    }

    public function delete(Usuario $usuario, MallaCurricular $malla): bool
    {
        if (!$usuario->hasPermissionTo('malla.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        if ($usuario->hasRole('Coordinador') && $this->coordinatesModality($usuario, $malla, 'grado.modalidad_id')) {
            return true;
        }

        return false;
    }
}