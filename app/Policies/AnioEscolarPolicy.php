<?php

namespace App\Policies;

use App\Models\AnioEscolar;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Support\Facades\Log;

class AnioEscolarPolicy
{
    use HasResourceOwnership;

    public function viewAny(Usuario $usuario): bool
    {
        try {
            return $usuario->can('configuracion.ver')
                || $usuario->can('configuracion.gestionar')
                || $this->isSuperAdmin($usuario);
        } catch (\Throwable $e) {
            Log::error('Error en AnioEscolarPolicy::viewAny', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function view(Usuario $usuario, AnioEscolar$anioEscolar): bool
    {
        return $this->viewAny($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->can('configuracion.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function update(Usuario $usuario, AnioEscolar$anioEscolar): bool
    {
        if (!$usuario->can('configuracion.gestionar') && !$this->isSuperAdmin($usuario)) {
            return false;
        }

        return !$anioEscolar->es_cerrado;
    }

    public function delete(Usuario $usuario, AnioEscolar$anioEscolar): bool
    {
        if (!$this->isDirectora($usuario)) {
            return false;
        }

        return $anioEscolar->es_planificacion;
    }

    public function cambiarEstado(Usuario $usuario, AnioEscolar$anioEscolar): bool
    {
        return $usuario->can('configuracion.gestionar') || $this->isSuperAdmin($usuario);
    }

    public function cerrar(Usuario $usuario, AnioEscolar$anioEscolar): bool
    {
        return ($usuario->can('configuracion.gestionar') || $this->isSuperAdmin($usuario))
            && $this->isSuperAdmin($usuario);
    }
}