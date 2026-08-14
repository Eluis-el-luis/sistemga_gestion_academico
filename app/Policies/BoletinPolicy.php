<?php

namespace App\Policies;

use App\Models\Boletin;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class BoletinPolicy
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
        return $usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('boletines.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, Boletin $boletin): bool
    {
        if (!$usuario->hasPermissionTo('boletines.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente) {
            // El boletín pertenece a una matrícula, que pertenece a un aula
            return $boletin->matricula->aula->docente_guia_id === $docente->id;
        }
        return false;
    }
}
