<?php

namespace App\Policies;

use App\Models\ExamenReparacion;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class ExamenReparacionPolicy
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
            return $usuario->hasPermissionTo('reparacion.ver')
                || $usuario->hasPermissionTo('reparacion.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'docente_guia']);
        } catch (\Throwable $e) {
            Log::error('Error en ExamenReparacionPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, ExamenReparacion $examen): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $examen, 'matricula.aula.modalidad_id')) {
                return true;
            }

            return $this->ownsViaMatriculaAula($usuario, $examen, 'matricula');
        } catch (\Throwable $e) {
            Log::error('Error en ExamenReparacionPolicy::view', [
                'user_id' => $usuario->id,
                'examen_id' => $examen->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('reparacion.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, ExamenReparacion $examen): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->ownsViaMatriculaAula($usuario, $examen, 'matricula')) {
                return true;
            }

            if (isset($examen->aula_asignatura_docente_id)) {
                return $this->ownsAsDocenteAsignatura($usuario, $examen, 'aulaAsignaturaDocente');
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Error en ExamenReparacionPolicy::update', [
                'user_id' => $usuario->id,
                'examen_id' => $examen->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, ExamenReparacion $examen): bool
    {
        return $this->isSuperAdmin($usuario);
    }
}