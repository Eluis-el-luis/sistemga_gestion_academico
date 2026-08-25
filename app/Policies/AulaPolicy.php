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
    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, Aula $aula): bool
    {
        // 1. Verificamos si el usuario está ligado a un registro de Docente
        $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
        
        // 2. Si es docente y tiene la bandera de coordinador activa...
        if ($docente && $docente->es_coordinador) {
            // Solo puede editar (agregar/quitar materias) si el aula pertenece a la modalidad que coordina
            return $docente->modalidad_coordina_id === $aula->modalidad_id;
        }

        // 3. Si no es coordinador, debe tener explícitamente el permiso de gestión (Dirección)
        return $usuario->hasPermissionTo('aulas.gestionar');
    }

    /**
     * Equivalente a (E)liminar en la Matriz.
     */
    public function delete(Usuario $usuario, Aula $aula): bool
    {
        // 1. Verificamos si es coordinador de la modalidad del aula
        $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
        
        if ($docente && $docente->es_coordinador) {
            return $docente->modalidad_coordina_id === $aula->modalidad_id;
        }

        // 2. Si no, requiere el permiso general de gestión
        return $usuario->hasPermissionTo('aulas.gestionar');
    }


}
