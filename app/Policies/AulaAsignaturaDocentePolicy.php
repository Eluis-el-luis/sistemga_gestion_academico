<?php

namespace App\Policies;

use App\Models\AulaAsignaturaDocente;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class AulaAsignaturaDocentePolicy
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
        return $usuario->hasPermissionTo('asignaturas_aula.ver') || $usuario->hasPermissionTo('asignaturas_aula.gestionar');
    }

    /**
     * Equivalente a (G)estionar en la Matriz.
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('asignaturas_aula.gestionar');
    }

    /**
     * Alcance de los datos (G)estionar.
     */
    public function update(Usuario $usuario, AulaAsignaturaDocente $aulaAsignaturaDocente): bool
    {
        if (!$usuario->hasPermissionTo('asignaturas_aula.gestionar')) {
            return false;
        }

        // Validación de alcance: El docente solo puede editar si él imparte esa clase
        $docente = $usuario->docente;
        if ($docente) {
            return $aulaAsignaturaDocente->docente_id === $docente->id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede ingresar calificaciones para esta asignatura.
     */
    public function calificar(Usuario $usuario, AulaAsignaturaDocente $asignatura): bool
    {
        // 1. Debe tener el permiso base de Spatie para gestionar notas
        if (!$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // 2. Verificamos la relación del docente con esta materia específica
        $docente = $usuario->docente;
        if ($docente) {
            // Regla A: ¿Es el maestro que imparte esta asignatura exacta?
            if ($asignatura->docente_id === $docente->id) {
                return true;
            }

            // Regla B: ¿Es el docente guía dueño de toda el aula?
            if ($asignatura->aula->docente_guia_id === $docente->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si el usuario puede gestionar asistencia para esta asignatura.
     */
    public function gestionarAsistencia(Usuario $usuario, AulaAsignaturaDocente $asignatura): bool
    {
        // 1. Debe tener el permiso base de Spatie para gestionar asistencia
        if (!$usuario->hasPermissionTo('asistencia.gestionar')) {
            return false;
        }

        // 2. Verificamos la relación del docente con esta materia específica
        $docente = $usuario->docente;
        if ($docente) {
            // Regla A: ¿Es el maestro que imparte esta asignatura exacta?
            if ($asignatura->docente_id === $docente->id) {
                return true;
            }

            // Regla B: ¿Es el docente guía dueño de toda el aula?
            if ($asignatura->aula->docente_guia_id === $docente->id) {
                return true;
            }
        }

        return false;
    }
}
