<?php

namespace App\Policies;

use App\Models\Horario;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class HorarioPolicy
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
            return $usuario->hasPermissionTo('horarios.ver')
                || $usuario->hasPermissionTo('horarios.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente', 'psicologa']);
        } catch (\Throwable $e) {
            Log::error('Error en HorarioPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Horario $horario): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, ['secretaria', 'psicologa'])) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $horario, 'aulaAsignaturaDocente.aula.modalidad_id')) {
                return true;
            }

            return $this->ownsViaAsignacion($usuario, $horario, 'aulaAsignaturaDocente');
        } catch (\Throwable $e) {
            Log::error('Error en HorarioPolicy::view', [
                'user_id' => $usuario->id,
                'horario_id' => $horario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('horarios.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, Horario $horario): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            return $this->ownsViaAsignacion($usuario, $horario, 'aulaAsignaturaDocente');
        } catch (\Throwable $e) {
            Log::error('Error en HorarioPolicy::update', [
                'user_id' => $usuario->id,
                'horario_id' => $horario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Horario $horario): bool
    {
        return $this->update($usuario, $horario);
    }
}