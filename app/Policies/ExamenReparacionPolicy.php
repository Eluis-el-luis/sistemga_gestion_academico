<?php

namespace App\Policies;

use App\Models\ExamenReparacion;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class ExamenReparacionPolicy
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
        return $usuario->hasPermissionTo('reparacion.ver') || $usuario->hasPermissionTo('reparacion.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('reparacion.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, ExamenReparacion $examenReparacion): bool
    {
        if (!$usuario->hasPermissionTo('reparacion.gestionar')) {
            return false;
        }

        // Validación de alcance: El docente solo puede editar si es guía del aula de la matrícula
        $docente = $usuario->docente;
        if ($docente) {
            return $examenReparacion->matricula->aula->docente_guia_id === $docente->id;
        }

        return false;
    }

    /**
     * Eliminar un examen de reparación.
     */
    public function delete(Usuario $usuario, ExamenReparacion $examenReparacion): bool
    {
        return $this->update($usuario, $examenReparacion);
    }
}
