<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\Usuario;

class NotaPolicy
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
        return $usuario->hasPermissionTo('notas.ver') || $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, Nota $nota): bool
    {
        if (!$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Validación de alcance: El docente solo puede editar si él imparte esa clase
        $docente = $usuario->docente;
        if ($docente) {
            return $nota->aulaAsignaturaDocente->docente_id === $docente->id;
        }

        return false;
    }
}