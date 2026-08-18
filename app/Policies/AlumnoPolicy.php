<?php

namespace App\Policies;

use App\Models\Alumno;
use App\Models\Usuario;

class AlumnoPolicy
{
    /**
     * Intercepta las validaciones.
     * Si es Director o Subdirector, tienen alcance sobre todo el colegio,
     * así que les damos pase libre (retornando true) para cualquier acción.
     */
    public function before(Usuario $usuario, $ability): ?bool
    {
        if ($usuario->hasRole(['Director', 'Subdirector'])) {
            return true;
        }
        
        return null; // Si no es ninguno de esos dos, continúa con las reglas de abajo
    }

    /**
     * ¿Puede ver la lista general de alumnos? (Index)
     */
    public function viewAny(Usuario $usuario): bool
    {
        // Todos los roles de la matriz tienen al menos una "V" o "G" en este módulo
        return $usuario->hasPermissionTo('alumnos.ver') || $usuario->hasPermissionTo('alumnos.gestionar');
    }

    public function create(Usuario $usuario): bool
    {
        // Solo Dirección y Subdirección pueden registrar nuevos ingresos en el sistema
        return $usuario->hasRole(['Director', 'Subdirector']);
    }

    /**
     * ¿Puede editar a un alumno en específico?
     */
    public function update(Usuario $usuario, Alumno $alumno): bool
    {
        if (!$usuario->hasRole(['Director', 'Subdirector'])) {
            return false;
        }

        // Aquí aplicamos el ALCANCE de los datos. 
        // Si el usuario es un Docente que funge como Coordinador,
        // solo puede editar alumnos que pertenezcan a la modalidad que él coordina.
        
        // Obtenemos el registro de docente del usuario actual
        $docente = $usuario->docente; 

        if ($docente && $docente->es_coordinador) {
            // Buscamos si el alumno tiene una matrícula activa en la modalidad que coordina este docente
            $matriculaActiva = $alumno->matriculas()
                ->where('estado', 'activo')
                ->whereHas('aula', function ($query) use ($docente) {
                    $query->where('modalidad_id', $docente->modalidad_coordina_id);
                })->exists();

            return $matriculaActiva;
        }

        return false;
    }

    public function delete(Usuario $usuario, Alumno $alumno): bool
    {
        return $usuario->hasRole(['Director', 'Subdirector']);
    }
}