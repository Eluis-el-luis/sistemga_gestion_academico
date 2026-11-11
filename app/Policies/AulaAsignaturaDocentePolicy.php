<?php

namespace App\Policies;

use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class AulaAsignaturaDocentePolicy
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
        return $usuario->hasPermissionTo('asignaturas_aula.ver') || $usuario->hasPermissionTo('asignaturas_aula.gestionar');
    }

    public function view(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        if (!$usuario->hasPermissionTo('asignaturas_aula.ver') && !$usuario->hasPermissionTo('asignaturas_aula.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        if ($usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        if ($usuario->hasRole('Coordinador')) {
            return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
        }

        // Docente guía del aula
        if ($this->ownsAula($usuario, $asignacion, 'aula')) {
            return true;
        }

        // Docente que imparte esta asignatura
        return $this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asignaturas_aula.gestionar');
    }

    public function update(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        if (!$usuario->hasPermissionTo('asignaturas_aula.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Coordinador: solo su modalidad
        if ($this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id')) {
            return true;
        }

        // Docente puede actualizar su propia asignación
        return $this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion);
    }

    public function delete(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        if (!$usuario->hasPermissionTo('asignaturas_aula.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario) || $usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
    }

    /**
     * Determina si el usuario puede ingresar calificaciones para esta asignatura.
     * Requiere permiso notas.gestionar Y ownership de la asignación.
     */
    public function calificar(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        if (!$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Super-admin puede calificar todo
        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente que imparte la asignatura exacta
        if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) {
            return true;
        }

        // Docente guía del aula (tiene acceso a todas las materias de su aula)
        if ($this->ownsAula($usuario, $asignacion, 'aula')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede gestionar asistencia para esta asignatura.
     */
    public function gestionarAsistencia(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        if (!$usuario->hasPermissionTo('asistencia.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) {
            return true;
        }

        if ($this->ownsAula($usuario, $asignacion, 'aula')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede VER (solo lectura) las actividades evaluativas.
     * Para Coordinador: acceso de supervisión a su modalidad sin permiso notas.gestionar
     * Para Directiva: acceso total
     * Para Docentes: ownership normal
     */
    public function ver(Usuario $usuario, AulaAsignaturaDocente $asignacion): bool
    {
        // Directiva: acceso total
        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Coordinador: supervisión de su modalidad (solo lectura)
        if ($usuario->hasRole('Coordinador')) {
            return $this->coordinatesModality($usuario, $asignacion, 'aula.modalidad_id');
        }

        // Gestor de Usuarios: acceso total
        if ($usuario->hasRole('Gestor de Usuarios')) {
            return true;
        }

        // Docente que imparte la asignatura exacta
        if ($this->ownsAsDocenteAsignaturaDirect($usuario, $asignacion)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsAula($usuario, $asignacion, 'aula')) {
            return true;
        }

        return false;
    }
}