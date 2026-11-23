<?php

namespace App\Policies;

use App\Models\Alumno;
use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AlumnoPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, string$ability): ?bool
    {
        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        try {
            return $usuario->hasPermissionTo('alumnos.ver')
                || $usuario->hasPermissionTo('alumnos.gestionar')
                || $this->hasRoleKey($usuario, ['secretaria', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'psicologa']);
        } catch (\Throwable $e) {
            Log::error('Error en AlumnoPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Alumno$alumno): bool
    {
        try {
            if ($this->hasRoleKey($usuario, ['secretaria', 'psicologa'])) {
                return true;
            }

            if ($this->ownsAsAlumno($usuario,$alumno, 'usuario_id')) {
                return true;
            }

            $docente =$usuario->docente;
            if (!$docente) {
                return false;
            }

            if ($docente->es_coordinador &&$docente->modalidad_coordina_id) {
                return $alumno->matriculas()
                    ->where('estado', 'activo')
                    ->whereHas('aula', fn ($q) => $q->where('modalidad_id',$docente->modalidad_coordina_id))
                    ->exists();
            }

            $esGuia =$alumno->matriculas()
                ->where('estado', 'activo')
                ->whereHas('aula', fn ($q) => $q->where('docente_guia_id',$docente->id))
                ->exists();

            if ($esGuia) {
                return true;
            }

            $aulaIds =$alumno->matriculas()
                ->where('estado', 'activo')
                ->pluck('aula_id');

            return AulaAsignaturaDocente::whereIn('aula_id', $aulaIds)
                ->where('docente_id', $docente->id)
                ->where('activo', true)
                ->exists();
        } catch (\Throwable $e) {
            Log::error('Error en AlumnoPolicy::view', [
                'user_id' => $usuario->id,
                'alumno_id' => $alumno->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        try {
            return $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria']);
        } catch (\Throwable $e) {
            Log::error('Error en AlumnoPolicy::create', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function update(Usuario $usuario, Alumno$alumno): bool
    {
        try {
            if ($this->hasRoleKey($usuario, 'secretaria')) {
                return true;
            }

            $docente =$usuario->docente;
            if ($docente && $docente->es_coordinador &&$docente->modalidad_coordina_id) {
                return $alumno->matriculas()
                    ->where('estado', 'activo')
                    ->whereHas('aula', fn ($q) => $q->where('modalidad_id',$docente->modalidad_coordina_id))
                    ->exists();
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en AlumnoPolicy::update', [
                'user_id' => $usuario->id,
                'alumno_id' => $alumno->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Alumno$alumno): bool
    {
        return $this->isSuperAdmin($usuario);
    }
}