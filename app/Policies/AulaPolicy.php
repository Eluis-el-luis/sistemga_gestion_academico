<?php

namespace App\Policies;

use App\Models\Aula;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class AulaPolicy
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
        return $usuario->hasPermissionTo('aulas.ver') || $usuario->hasPermissionTo('aulas.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('aulas.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, Aula $aula): bool
    {
        // Nota: En el seeder le pusimos 'alumnos.gestionar' a este bloque
        if (!$usuario->hasPermissionTo('aulas.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente && $docente->es_coordinador) {
            // Solo puede editar si la matrícula pertenece a un aula de SU modalidad
            return $docente->modalidad_coordina_id === $aula->modalidad_id;
        }
        return false;
    }
}
