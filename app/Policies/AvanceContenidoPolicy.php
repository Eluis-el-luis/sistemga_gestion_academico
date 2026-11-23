<?php

namespace App\Policies;

use App\Models\AvanceContenido;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AvanceContenidoPolicy
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
            return $usuario->hasPermissionTo('avance.ver')
                || $usuario->hasPermissionTo('avance.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'coordinador', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente']);
        } catch (\Throwable $e) {
            Log::error('Error en AvanceContenidoPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, AvanceContenido $avance): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $avance, 'aulaAsignaturaDocente.aula.modalidad_id')) {
                return true;
            }

            return $this->ownsViaAsignacion($usuario, $avance, 'aulaAsignaturaDocente');
        } catch (\Throwable $e) {
            Log::error('Error en AvanceContenidoPolicy::view', [
                'user_id' => $usuario->id,
                'avance_id' => $avance->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('avance.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'docente_guia', 'docente_asignatura', 'docente_auxiliar', 'docente_suplente']);
    }

    public function update(Usuario $usuario, AvanceContenido $avance): bool
    {
        try {
            if ($this->isSuperAdmin($usuario)) {
                return true;
            }

            return $this->ownsViaAsignacion($usuario, $avance, 'aulaAsignaturaDocente');
        } catch (\Throwable $e) {
            Log::error('Error en AvanceContenidoPolicy::update', [
                'user_id' => $usuario->id,
                'avance_id' => $avance->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, AvanceContenido $avance): bool
    {
        return $this->update($usuario, $avance);
    }
}