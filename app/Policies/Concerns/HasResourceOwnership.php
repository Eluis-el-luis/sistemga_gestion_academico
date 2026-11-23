<?php

namespace App\Policies\Concerns;

use App\Models\CorteCerrado;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

trait HasResourceOwnership
{
    private const ROLE_ALIASES = [
        'directora' => ['directora', 'Director'],
        'subdirectora' => ['subdirectora', 'Subdirector'],
        'secretaria' => ['secretaria', 'Secretaria'],
        'coordinador' => ['coordinador', 'Coordinador'],
        'docente_guia' => ['docente_guia', 'Docente Guia', 'Docente Guía'],
        'docente_asignatura' => ['docente_asignatura', 'Docente por Asignatura'],
        'docente_auxiliar' => ['docente_auxiliar', 'Docente Auxiliar'],
        'docente_suplente' => ['docente_suplente', 'Docente Suplente'],
        'psicologa' => ['psicologa', 'Psicologa', 'Psicóloga'],
        'gestor_it' => ['gestor_it', 'Gestor de Usuarios'],
    ];

    protected function hasRoleKey(Usuario $usuario, string|array $roleKeys): bool
    {
        try {
            $keys = is_array($roleKeys) ? $roleKeys : [$roleKeys];
            $expanded = [];

            foreach ($keys as $key) {
                $normalized = strtolower(trim($key));
                if (isset(self::ROLE_ALIASES[$normalized])) {
                    array_push($expanded, ...self::ROLE_ALIASES[$normalized]);
                } else {
                    $expanded[] = $key;
                }
            }

            return $usuario->hasAnyRole($expanded);
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::hasRoleKey', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function isSuperAdmin(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, ['directora', 'subdirectora']);
    }

    protected function isDirectora(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, 'directora');
    }

    protected function isSubdirectora(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, 'subdirectora');
    }

    protected function isGestorIt(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, 'gestor_it');
    }

    protected function isSecretaria(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, 'secretaria');
    }

    protected function isCoordinador(Usuario $usuario): bool
    {
        return $this->hasRoleKey($usuario, 'coordinador');
    }

    protected function ownsViaAsignacion(Usuario $usuario, $resource, string $asignacionRelation): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente) {
                return false;
            }

            $asignacion = $resource->{$asignacionRelation} ?? $resource->{$asignacionRelation}()->first();
            if (!$asignacion) {
                return false;
            }

            if ((int) $asignacion->docente_id === (int) $docente->id) {
                return true;
            }

            return (int) ($asignacion->aula?->docente_guia_id ?? 0) === (int) $docente->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsViaAsignacion', [
                'user_id' => $usuario->id,
                'relation' => $asignacionRelation,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function ownsViaMatriculaAula(Usuario $usuario, $resource, string $matriculaRelation): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente) {
                return false;
            }

            $matricula = $resource->{$matriculaRelation} ?? $resource->{$matriculaRelation}()->first();
            if (!$matricula) {
                return false;
            }

            return (int) ($matricula->aula?->docente_guia_id ?? 0) === (int) $docente->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsViaMatriculaAula', [
                'user_id' => $usuario->id,
                'relation' => $matriculaRelation,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function ownsAula(Usuario $usuario, $resource, string $aulaRelation = 'aula'): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente) {
                return false;
            }

            $aula = $resource->{$aulaRelation} ?? $resource->{$aulaRelation}()->first();
            if (!$aula) {
                return false;
            }

            return (int) $aula->docente_guia_id === (int) $docente->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsAula', [
                'user_id' => $usuario->id,
                'relation' => $aulaRelation,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function coordinatesModality(Usuario $usuario, $resource, string $modalidadPath): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente || !$docente->es_coordinador || !$docente->modalidad_coordina_id) {
                return false;
            }

            $modalidadId = data_get($resource, $modalidadPath);
            if (!$modalidadId) {
                return false;
            }

            return (int) $docente->modalidad_coordina_id === (int) $modalidadId;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::coordinatesModality', [
                'user_id' => $usuario->id,
                'path' => $modalidadPath,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function isCorteBloqueado($resource, string $asignacionRelation, string $corteRelation): bool
    {
        try {
            $asignacionId = data_get($resource, "{$asignacionRelation}.id") ?? data_get($resource, 'aula_asignatura_docente_id');
            $corteId = data_get($resource, "{$corteRelation}.id") ?? data_get($resource, 'corte_evaluativo_id');

            if (!$asignacionId || !$corteId) {
                return false;
            }

            return CorteCerrado::where('aula_asignatura_docente_id', (int) $asignacionId)
                ->where('corte_evaluativo_id', (int) $corteId)
                ->where('bloqueado', true)
                ->exists();
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::isCorteBloqueado', [
                'exception' => $e->getMessage(),
            ]);

            return true;
        }
    }

    protected function ownsAsDocenteAsignatura(Usuario $usuario, $resource, string $asignacionRelation): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente) {
                return false;
            }

            $asignacion = $resource->{$asignacionRelation} ?? $resource->{$asignacionRelation}()->first();
            if (!$asignacion) {
                return false;
            }

            return (int) $asignacion->docente_id === (int) $docente->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsAsDocenteAsignatura', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function ownsAsDocenteAsignaturaDirect(Usuario $usuario, $asignacion): bool
    {
        try {
            $docente = $usuario->docente;
            if (!$docente || !$asignacion) {
                return false;
            }

            return (int) $asignacion->docente_id === (int) $docente->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsAsDocenteAsignaturaDirect', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function ownsAsAlumno(Usuario $usuario, $resource, string $alumnoPath): bool
    {
        try {
            $alumnoUsuarioId = data_get($resource, $alumnoPath);
            return $alumnoUsuarioId && (int) $alumnoUsuarioId === (int) $usuario->id;
        } catch (\Throwable $e) {
            Log::error('Error en HasResourceOwnership::ownsAsAlumno', [
                'user_id' => $usuario->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}