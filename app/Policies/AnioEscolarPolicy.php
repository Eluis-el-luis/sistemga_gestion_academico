<?php

namespace App\Policies;

use App\Models\AnioEscolar;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class AnioEscolarPolicy
{
    /**
     * Determina si el usuario puede ver cualquier año escolar.
     */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->can('configuracion.ver') || $usuario->can('configuracion.gestionar');
    }

    /**
     * Determina si el usuario puede ver un año escolar específico.
     */
    public function view(Usuario $usuario, AnioEscolar $anioEscolar): bool
    {
        return $usuario->can('configuracion.ver') || $usuario->can('configuracion.gestionar');
    }

    /**
     * Determina si el usuario puede crear años escolares.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->can('configuracion.gestionar');
    }

    /**
     * Determina si el usuario puede actualizar un año escolar.
     */
    public function update(Usuario $usuario, AnioEscolar $anioEscolar): bool
    {
        // Solo si tiene permiso de gestionar Y el año no está cerrado
        if (!$usuario->can('configuracion.gestionar')) {
            return false;
        }

        // No se puede editar un año cerrado
        return !$anioEscolar->es_cerrado;
    }

    /**
     * Determina si el usuario puede eliminar un año escolar.
     */
    public function delete(Usuario $usuario, AnioEscolar $anioEscolar): bool
    {
        // Solo Director puede eliminar, y solo años en planificación
        if (!$usuario->can('configuracion.gestionar') || !$usuario->hasRole('Director')) {
            return false;
        }

        // Solo se puede eliminar si está en planificación
        return $anioEscolar->es_planificacion;
    }

    /**
     * Determina si el usuario puede cambiar el estado del año escolar.
     */
    public function cambiarEstado(Usuario $usuario, AnioEscolar $anioEscolar): bool
    {
        return $usuario->can('configuracion.gestionar');
    }

    /**
     * Determina si el usuario puede cerrar el año escolar.
     * Solo Director/Subdirector pueden ejecutar el cierre definitivo.
     */
    public function cerrar(Usuario $usuario, AnioEscolar $anioEscolar): bool
    {
        return $usuario->can('configuracion.gestionar') 
            && ($usuario->hasRole('Director') || $usuario->hasRole('Subdirector'));
    }
}