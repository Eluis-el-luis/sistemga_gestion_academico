<?php

namespace App\Policies;

use App\Models\ApoyoPadres;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class ApoyoPadresPolicy
{
    /**
     * El "Poder Absoluto" para Dirección y Subdirección.
     * Al poner esto aquí, te ahorras validarlos en los demás métodos.
     */
    public function before(Usuario $usuario, $ability): ?bool
    {
        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            return true;
        }
        return null;
    }

    /**
     * Equivalente a (V)er en la Matriz.
     */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('apoyo_padres.ver') || $usuario->hasPermissionTo('apoyo_padres.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('apoyo_padres.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, ApoyoPadres $apoyoPadres): bool
    {
        if (!$usuario->hasPermissionTo('apoyo_padres.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente) {
            // Verificamos si el docente es el guía del aula de este registro
            return $apoyoPadres->aula->docente_guia_id === $docente->id;
        }
        return false;
    }

    /**
     * Eliminar un registro de apoyo de padres.
     */
    public function delete(Usuario $usuario, ApoyoPadres $apoyoPadres): bool
    {
        return $this->update($usuario, $apoyoPadres);
    }
}
