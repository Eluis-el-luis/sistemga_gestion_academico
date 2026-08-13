<?php

namespace App\Policies;

use App\Models\AsistenciaAula;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class AsistenciaAulaPolicy
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
        return $usuario->hasPermissionTo('asistencia.ver') || $usuario->hasPermissionTo('asistencia.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asistencia.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, AsistenciaAula $asistenciaAula): bool
    {
        if (!$usuario->hasPermissionTo('asistencia.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente) {
            // Navegamos: Asistencia -> Matricula -> Aula -> docente_guia_id
            return $asistenciaAula->matricula->aula->docente_guia_id === $docente->id;
        }
        return false;
    }
}
