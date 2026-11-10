<?php

namespace App\Policies;

use App\Models\AsistenciaAula;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;

class AsistenciaAulaPolicy
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
        return $usuario->hasPermissionTo('asistencia.ver') || $usuario->hasPermissionTo('asistencia.gestionar');
    }

    public function view(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        if (!$usuario->hasPermissionTo('asistencia.ver') && !$usuario->hasPermissionTo('asistencia.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsViaMatriculaAula($usuario, $asistencia, 'matricula')) {
            return true;
        }

        // Docente por asignatura que imparte en ese aula
        $docente = $usuario->docente;
        if ($docente && $asistencia->matricula->aula) {
            return \App\Models\AulaAsignaturaDocente::where('aula_id', $asistencia->matricula->aula_id)
                ->where('docente_id', $docente->id)
                ->where('activo', true)
                ->exists();
        }

        // Alumno ve su propia asistencia
        return $this->ownsAsAlumno($usuario, $asistencia, 'matricula.alumno.usuario_id');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asistencia.gestionar');
    }

    public function update(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        if (!$usuario->hasPermissionTo('asistencia.gestionar')) {
            return false;
        }

        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Docente guía del aula
        if ($this->ownsViaMatriculaAula($usuario, $asistencia, 'matricula')) {
            return true;
        }

        // Docente por asignatura que imparte en ese aula
        $docente = $usuario->docente;
        if ($docente && $asistencia->matricula->aula) {
            return \App\Models\AulaAsignaturaDocente::where('aula_id', $asistencia->matricula->aula_id)
                ->where('docente_id', $docente->id)
                ->where('activo', true)
                ->exists();
        }

        return false;
    }

    public function delete(Usuario $usuario, AsistenciaAula $asistencia): bool
    {
        return $this->update($usuario, $asistencia);
    }
}