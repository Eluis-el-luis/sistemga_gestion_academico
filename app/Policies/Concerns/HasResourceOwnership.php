<?php

namespace App\Policies\Concerns;

use App\Models\Usuario;

trait HasResourceOwnership
{
    /**
     * Verifica si el usuario es super-admin (Director/Subdirector)
     * Estos roles tienen "poder absoluto" SOLO para lectura (viewAny, view)
     * NUNCA para create, update, delete - requieren ownership explícito
     */
    protected function isSuperAdmin(Usuario $usuario): bool
    {
        return $usuario->hasRole(['Director', 'Subdirector']);
    }

    /**
     * Verifica ownership genérico para recursos vinculados a AulaAsignaturaDocente
     * Aplicable a: notas, horarios, avance_contenido, examen_reparacion, etc.
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent (Nota, Horario, AvanceContenido, etc.)
     * @param string $asignacionRelation Nombre de la relación hacia AulaAsignaturaDocente (ej: 'aulaAsignaturaDocente')
     * @return bool
     */
    protected function ownsViaAsignacion(Usuario $usuario, $resource, string $asignacionRelation): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        // Cargar relación si no viene eager loaded
        $asignacion = $resource->{$asignacionRelation} ?? $resource->{$asignacionRelation}()->first();
        if (!$asignacion) {
            return false;
        }

        // Regla A: Es el docente que imparte ESTA asignatura exacta
        if ($asignacion->docente_id === $docente->id) {
            return true;
        }

        // Regla B: Es el docente guía del aula (tiene acceso a todas las materias de su aula)
        if ($asignacion->aula?->docente_guia_id === $docente->id) {
            return true;
        }

        return false;
    }

    /**
     * Verifica ownership para recursos vinculados a Matricula -> Aula
     * Aplicable a: boletines, matriculas, examenes_reparacion
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent (Boletin, Matricula, ExamenReparacion, etc.)
     * @param string $matriculaRelation Nombre de la relación hacia Matricula (ej: 'matricula')
     * @return bool
     */
    protected function ownsViaMatriculaAula(Usuario $usuario, $resource, string $matriculaRelation): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $matricula = $resource->{$matriculaRelation} ?? $resource->{$matriculaRelation}()->first();
        if (!$matricula) {
            return false;
        }

        // Docente guía del aula de la matrícula
        return $matricula->aula?->docente_guia_id === $docente->id;
    }

    /**
     * Verifica ownership para recursos vinculados a Aula directamente
     * Aplicable a: apoyo_padres, aulas, avance_contenido (via aula_asignatura_docente->aula)
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent
     * @param string $aulaRelation Nombre de la relación hacia Aula (default: 'aula')
     * @return bool
     */
    protected function ownsAula(Usuario $usuario, $resource, string $aulaRelation = 'aula'): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $aula = $resource->{$aulaRelation} ?? $resource->{$aulaRelation}()->first();
        if (!$aula) {
            return false;
        }

        return $aula->docente_guia_id === $docente->id;
    }

    /**
     * Verifica si el usuario coordina la modalidad del recurso
     * Aplicable a: Coordinador que supervisa todas las aulas de su modalidad
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent
     * @param string $modalidadPath Path para acceder a modalidad_id (ej: 'aula.modalidad_id', 'matricula.aula.modalidad_id')
     * @return bool
     */
    protected function coordinatesModality(Usuario $usuario, $resource, string $modalidadPath): bool
    {
        $docente = $usuario->docente;
        if (!$docente || !$docente->es_coordinador) {
            return false;
        }

        $modalidadId = data_get($resource, $modalidadPath);
        if (!$modalidadId) {
            return false;
        }

        return $docente->modalidad_coordina_id === $modalidadId;
    }

    /**
     * Verifica si el corte evaluativo está bloqueado para esta asignación
     * Previene modificaciones en cortes cerrados/bloqueados
     * 
     * @param mixed $resource Modelo Eloquent con relaciones a asignacion y corte
     * @param string $asignacionRelation Relación hacia AulaAsignaturaDocente
     * @param string $corteRelation Relación hacia CorteEvaluativo
     * @return bool
     */
    protected function isCorteBloqueado($resource, string $asignacionRelation, string $corteRelation): bool
    {
        $asignacionId = data_get($resource, "{$asignacionRelation}.id");
        $corteId = data_get($resource, "{$corteRelation}.id");

        if (!$asignacionId || !$corteId) {
            return false;
        }

        return \App\Models\CorteCerrado::where('aula_asignatura_docente_id', $asignacionId)
            ->where('corte_evaluativo_id', $corteId)
            ->where('bloqueado', true)
            ->exists();
    }

    /**
     * Verifica ownership para Docente por Asignatura
     * El docente solo accede a recursos de las asignaturas que imparte
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent
     * @param string $asignacionRelation Relación hacia AulaAsignaturaDocente
     * @return bool
     */
    protected function ownsAsDocenteAsignatura(Usuario $usuario, $resource, string $asignacionRelation): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        $asignacion = $resource->{$asignacionRelation} ?? $resource->{$asignacionRelation}()->first();
        if (!$asignacion) {
            return false;
        }

        // Solo si es el docente que imparte ESTA asignatura exacta
        // NO incluye docente guía (esa es ownsViaAsignacion)
        return $asignacion->docente_id === $docente->id;
    }

    /**
     * Verifica ownership directo para AulaAsignaturaDocente
     * Se usa cuando el recurso ES el AulaAsignaturaDocente mismo
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param \App\Models\AulaAsignaturaDocente $asignacion La asignación directa
     * @return bool
     */
    protected function ownsAsDocenteAsignaturaDirect(Usuario $usuario, \App\Models\AulaAsignaturaDocente $asignacion): bool
    {
        $docente = $usuario->docente;
        if (!$docente) {
            return false;
        }

        // Solo si es el docente que imparte ESTA asignatura exacta
        return $asignacion->docente_id === $docente->id;
    }

    /**
     * Verifica ownership para Alumno (acceso a sus propios datos)
     * 
     * @param Usuario $usuario Usuario autenticado
     * @param mixed $resource Modelo Eloquent con relación a alumno/usuario
     * @param string $alumnoPath Path hacia alumno.usuario_id (ej: 'matricula.alumno.usuario_id', 'alumno.usuario_id')
     * @return bool
     */
    protected function ownsAsAlumno(Usuario $usuario, $resource, string $alumnoPath): bool
    {
        $alumnoUsuarioId = data_get($resource, $alumnoPath);
        return $alumnoUsuarioId && $alumnoUsuarioId === $usuario->id;
    }
}