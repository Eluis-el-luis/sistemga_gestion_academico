<?php

namespace App\Policies;

use App\Models\MallaCurricular;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class MallaCurricularPolicy
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
        return $usuario->hasPermissionTo('mallas_curriculares.ver') || $usuario->hasPermissionTo('mallas_curriculares.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('mallas_curriculares.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, MallaCurricular $mallaCurricular): bool
    {
        // Como el 'before()' ya le dio acceso libre al Director y Subdirector,
        // y ningún otro rol tiene permiso de gestionar la malla en el Seeder,
        // basta con verificar el permiso de Spatie.
        return $usuario->hasPermissionTo('malla.gestionar');
    }
}
