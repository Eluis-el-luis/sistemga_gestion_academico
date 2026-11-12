<?php

namespace App\Policies;

use App\Models\ExamenReparacion;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class ExamenReparacionPolicy
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
        return $usuario->hasPermissionTo('reparacion.ver') || $usuario->hasPermissionTo('reparacion.gestionar');
    }

    public function view(Usuario $usuario, ExamenReparacion $examen): bool
    {
        if (!$usuario->hasPermissionTo('reparacion.ver') && !$usuario->hasPermissionTo('reparacion.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula de la matrícula
        if ($this->ownsViaMatriculaAula($usuario, $examen, 'matricula')) {
            return true;
        }

        // Docente por asignatura que imparte la materia del examen
        // (asumiendo que examen tiene relación con aula_asignatura_docente)
        // Por ahora solo docente guía
        return false;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('reparacion.gestionar');
    }

    public function update(Usuario $usuario, ExamenReparacion $examen): bool
    {
        if (!$usuario->hasPermissionTo('reparacion.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsViaMatriculaAula($usuario, $examen, 'matricula')) {
            return true;
        }

        // Docente por asignatura que imparte la materia
        // Si ExamenReparacion tiene aula_asignatura_docente_id:
        if (isset($examen->aula_asignatura_docente_id)) {
            return $this->ownsAsDocenteAsignatura($usuario, $examen, 'aulaAsignaturaDocente');
        }

        return false;
    }

    public function delete(Usuario $usuario, ExamenReparacion $examen): bool
    {
        return $this->update($usuario, $examen);
    }
}