<?php

namespace App\Policies;

use App\Models\AulaAsignaturaDocente;
use App\Models\Matricula;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class MatriculaPolicy
{
    use HasResourceOwnership;

    public function before(Usuario $usuario, string $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view'], true) && $this->isSuperAdmin($usuario)) {
            return true;
        }

        return null;
    }

    public function viewAny(Usuario $usuario): bool
    {
        try {
            return $usuario->hasPermissionTo('alumnos.ver')
                || $usuario->hasPermissionTo('alumnos.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'psicologa']);
        } catch (\Throwable $e) {
            Log::error('Error en MatriculaPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Matricula $matricula): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, ['secretaria', 'psicologa'])) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
                return true;
            }

            if ($this->ownsAula($usuario, $matricula, 'aula')) {
                return true;
            }

            $docente = $usuario->docente;
            if ($docente && $matricula->aula) {
                return AulaAsignaturaDocente::where('aula_id', $matricula->aula_id)
                    ->where('docente_id', $docente->id)
                    ->where('activo', true)
                    ->exists();
            }

            return $this->ownsAsAlumno($usuario, $matricula, 'alumno.usuario_id');
        } catch (\Throwable $e) {
            Log::error('Error en MatriculaPolicy::view', [
                'user_id' => $usuario->id,
                'matricula_id' => $matricula->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('alumnos.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria']);
    }

    public function update(Usuario $usuario, Matricula $matricula): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isSecretaria($usuario) || $this->isGestorIt($usuario)) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $matricula, 'aula.modalidad_id')) {
                return true;
            }

            return $this->ownsAula($usuario, $matricula, 'aula');
        } catch (\Throwable $e) {
            Log::error('Error en MatriculaPolicy::update', [
                'user_id' => $usuario->id,
                'matricula_id' => $matricula->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Matricula $matricula): bool
    {
        return $this->isSuperAdmin($usuario) || $this->isSecretaria($usuario);
    }
}