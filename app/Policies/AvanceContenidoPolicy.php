<?php

namespace App\Policies;

use App\Models\AvanceContenido;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class AvanceContenidoPolicy
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
        return $usuario->hasPermissionTo('avance.ver') || $usuario->hasPermissionTo('avance.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('avance.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, AvanceContenido $avanceContenido): bool
    {
        if (!$usuario->hasPermissionTo('avance.gestionar')) {
            return false;
        }

        // Validación de alcance: El docente solo puede editar si él imparte esa clase
        $docente = $usuario->docente;
        if ($docente) {
            return $avanceContenido->aulaAsignaturaDocente->docente_id === $docente->id;
        }

        return false;
    }

    /**
     * Eliminar un registro de avance.
     */
    public function delete(Usuario $usuario, AvanceContenido $avanceContenido): bool
    {
        return $this->update($usuario, $avanceContenido);
    }
}
