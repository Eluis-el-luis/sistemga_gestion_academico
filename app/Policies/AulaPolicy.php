<?php

namespace App\Policies;

use App\Models\Aula;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class AulaPolicy
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
        return $usuario->hasPermissionTo('aulas.ver') || $usuario->hasPermissionTo('aulas.gestionar');
    }

    public function view(Usuario $usuario, Aula $aula): bool
    {
        if (!$usuario->hasPermissionTo('aulas.ver') && !$usuario->hasPermissionTo('aulas.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Gestor de Usuarios, Secretaria, Coordinador ven todas (o su modalidad)
        if ($usuario->hasAnyRole(['Gestor de Usuarios', 'Secretaria'])) {
            return true;
        }

        if ($usuario->hasRole('Coordinador')) {
            return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
        }

        // Docente Guía: solo su aula
        if ($this->ownsAula($usuario, $aula)) {
            return true;
        }

        // Docente por Asignatura: ve aulas donde imparte
        $docente = $usuario->docente;
        if ($docente) {
            return \App\Models\AulaAsignaturaDocente::where('aula_id', $aula->id)
                ->where('docente_id', $docente->id)
                ->where('activo', true)
                ->exists();
        }

        return false;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('aulas.gestionar');
    }

    public function update(Usuario $usuario, Aula $aula): bool
    {
        if (!$usuario->hasPermissionTo('aulas.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Coordinador: solo aulas de su modalidad
        return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
    }

    public function delete(Usuario $usuario, Aula $aula): bool
    {
        if (!$usuario->hasPermissionTo('aulas.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Coordinador: solo aulas de su modalidad
        return $this->coordinatesModality($usuario, $aula, 'modalidad_id');
    }
}