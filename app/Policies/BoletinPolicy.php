<?php

namespace App\Policies;

use App\Models\Boletin;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class BoletinPolicy
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
            return $usuario->hasPermissionTo('boletines.ver')
                || $usuario->hasPermissionTo('boletines.gestionar')
                || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'coordinador', 'docente_guia', 'gestor_it']);
        } catch (\Throwable $e) {
            Log::error('Error en BoletinPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, Boletin $boletin): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->hasRoleKey($usuario, ['secretaria', 'gestor_it'])) {
                return true;
            }

            if ($this->coordinatesModality($usuario, $boletin, 'matricula.aula.modalidad_id')) {
                return true;
            }

            return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula');
        } catch (\Throwable $e) {
            Log::error('Error en BoletinPolicy::view', [
                'user_id' => $usuario->id,
                'boletin_id' => $boletin->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('boletines.gestionar')
            || $this->hasRoleKey($usuario, ['directora', 'subdirectora', 'secretaria', 'docente_guia', 'gestor_it']);
    }

    public function update(Usuario $usuario, Boletin $boletin): bool
    {
        try {
            if ($this->isSuperAdmin($usuario) || $this->isSecretaria($usuario)) {
                return true;
            }

            return $this->ownsViaMatriculaAula($usuario, $boletin, 'matricula')
                || $this->coordinatesModality($usuario, $boletin, 'matricula.aula.modalidad_id');
        } catch (\Throwable $e) {
            Log::error('Error en BoletinPolicy::update', [
                'user_id' => $usuario->id,
                'boletin_id' => $boletin->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Usuario $usuario, Boletin $boletin): bool
    {
        return $this->isSuperAdmin($usuario);
    }
}