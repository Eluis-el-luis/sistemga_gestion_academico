<?php

namespace App\Policies;

use App\Models\Nota;
use App\Models\Usuario;
use App\Policies\Concerns\HasResourceOwnership;
use Illuminate\Auth\Access\Response;

class NotaPolicy
{
    use HasResourceOwnership;

    /**
     * DENEGAR POR DEFECTO: before() SOLO retorna true para super-admin en ACCIONES DE LECTURA
     * NUNCA para create, update, delete - requieren verificación de ownership explícita
     */
    public function before(Usuario $usuario, $ability): ?bool
    {
        if (in_array($ability, ['viewAny', 'view']) && $this->isSuperAdmin($usuario)) {
            return true;
        }
        return null;
    }

    /**
     * Ver lista de notas - Policy solo verifica permiso BASE
     * El filtrado por ownership se hace en el controlador via Scope
     */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.ver') || $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Ver nota individual - Requiere ownership O permiso de supervisión
     */
    public function view(Usuario $usuario, Nota $nota): bool
    {
        // Permiso base
        if (!$usuario->hasPermissionTo('notas.ver') && !$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Super-admin (Director/Subdirector) ve todo
        if ($this->isSuperAdmin($usuario)) {
            return true;
        }

        // Verificar ownership vía relación
        return $this->checkOwnership($usuario, $nota);
    }

    /**
     * Crear nota - Requiere permiso gestionar Y ownership de la asignación
     * La verificación de ownership real se hace en update() con el recurso concreto
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->hasPermissionTo('notas.gestionar');
    }

    /**
     * Actualizar nota - Ownership estricto + verificación de corte no bloqueado
     */
    public function update(Usuario $usuario, Nota $nota): bool
    {
        if (!$usuario->hasPermissionTo('notas.gestionar')) {
            return false;
        }

        // Director/Subdirector: poder absoluto PERO solo si corte no bloqueado
        if ($this->isSuperAdmin($usuario)) {
            return !$this->isCorteBloqueado($nota, 'aulaAsignaturaDocente', 'corteEvaluativo');
        }

        // Docentes: ownership estricto + corte no bloqueado
        return $this->checkOwnership($usuario, $nota) 
            && !$this->isCorteBloqueado($nota, 'aulaAsignaturaDocente', 'corteEvaluativo');
    }

    /**
     * Eliminar nota - Solo Director/Subdirector, nunca docentes
     * Y solo si corte no está bloqueado
     */
    public function delete(Usuario $usuario, Nota $nota): bool
    {
        if (!$this->isSuperAdmin($usuario)) {
            return false;
        }
        return !$this->isCorteBloqueado($nota, 'aulaAsignaturaDocente', 'corteEvaluativo');
    }

    /**
     * Verificar ownership: El usuario es docente de esta asignación
     * O es docente guía del aula de la matrícula
     * Usa el trait HasResourceOwnership
     */
    protected function checkOwnership(Usuario $usuario, Nota $nota): bool
    {
        return $this->ownsViaAsignacion($usuario, $nota, 'aulaAsignaturaDocente');
    }
}