<?php

namespace App\Policies;

use App\Models\Matricula;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class MatriculaPolicy
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
        return $usuario->hasPermissionTo('alumnos.ver') || $usuario->hasPermissionTo('alumnos.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('alumnos.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, Matricula $matricula): bool
    {
        // Nota: En el seeder le pusimos 'alumnos.gestionar' a este bloque
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente && $docente->es_coordinador) {
            // Solo puede editar si la matrícula pertenece a un aula de SU modalidad
            return $docente->modalidad_coordina_id === $matricula->aula->modalidad_id;
        }
        return false;
    }

    /**
     * Ver una matrícula (incluye boletín del alumno).
     */
    public function view(Usuario $usuario, Matricula $matricula): bool
    {
        if ($usuario->hasPermissionTo('boletines.ver') || $usuario->hasPermissionTo('boletines.gestionar')) {
            return true;
        }

        $docente = $usuario->docente;
        if ($docente) {
            // Coordinador: solo su modalidad
            if ($docente->es_coordinador) {
                return $docente->modalidad_coordina_id === $matricula->aula->modalidad_id;
            }
            // Docente guía: solo su aula
            return $matricula->aula->docente_guia_id === $docente->id;
        }

        return false;
    }

    /**
     * Eliminar matrícula (soft delete).
     */
    public function delete(Usuario $usuario, Matricula $matricula): bool
    {
        if (!$usuario->hasPermissionTo('alumnos.gestionar')) return false;

        $docente = $usuario->docente;
        if ($docente && $docente->es_coordinador) {
            return $docente->modalidad_coordina_id === $matricula->aula->modalidad_id;
        }
        return false;
    }
}
