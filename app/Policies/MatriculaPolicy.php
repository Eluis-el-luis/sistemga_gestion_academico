<?php

namespace App\Policies;

use App\Models\Matricula;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class MatriculaPolicy
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
        return $usuario->hasPermissionTo('alumnos.ver') || $usuario->hasPermissionTo('alumnos.gestionar');
    }

    public function view(Usuario $usuario, Matricula $matricula): bool
    {
        // Permiso base
        if (!$usuario->hasPermissionTo('alumnos.ver') && !$usuario->hasPermissionTo('alumnos.gestionar')
            && !$usuario->hasPermissionTo('boletines.ver') && !$usuario->hasPermissionTo('boletines.gestionar')) {
            return false;
        }

        // Super-admin ve todo
        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Coordinador/Secretaria con permiso boletines: ven matrículas de su modalidad
        if (($usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar'))
            && $this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
            return true;
        }

        // Docente guía: ve su aula
        if ($this->ownsAula($usuario, $matricula, 'aula')) {
            return true;
        }

        // Docente por asignatura: ve si imparte en ese aula
        $docente = $usuario->docente;
        if ($docente && $matricula->aula) {
            return \App\Models\AulaAsignaturaDocente::where('aula_id', $matricula->aula_id)
                ->where('docente_id', $docente->id)
                ->where('activo', true)
                ->exists();
        }

        // Alumno ve su propia matrícula
        return $this->ownsAsAlumno($usuario, $matricula, 'alumno.usuario_id');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('alumnos.gestionar');
    }

    public function update(Usuario $usuario, Matricula $matricula): bool
    {
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Secretaria') || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Coordinador: solo su modalidad
        if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
            return true;
        }

        // Docente guía: solo su aula
        return $this->ownsAula($usuario, $matricula, 'aula');
    }

    public function delete(Usuario $usuario, Matricula $matricula): bool
    {
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Secretaria') || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
            return true;
        }

        return $this->ownsAula($usuario, $matricula, 'aula');
    }
}