<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleScope
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

    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        try {
            $user = Auth::user();

            if (!$user instanceof Usuario) {
                abort(401);
            }

            if ($allowedRoles === []) {
                return $next($request);
            }

            $expandedRoles = [];
            foreach ($allowedRoles as $role) {
                $key = strtolower(trim($role));
                if (isset(self::ROLE_ALIASES[$key])) {
                    array_push($expandedRoles, ...self::ROLE_ALIASES[$key]);
                } else {
                    $expandedRoles[] = $role;
                }
            }

            $hasRole = $user->roles()->pluck('name')->intersect($expandedRoles)->isNotEmpty();

            if (!$hasRole) {
                Log::warning('Intento de acceso bloqueado por EnsureRoleScope', [
                    'user_id' => $user->id,
                    'route' => $request->route()?->getName(),
                    'required_roles' => $allowedRoles,
                    'user_roles' => $user->getRoleNames()->toArray(),
                ]);

                abort(403, 'No tienes permisos para acceder a este módulo.');
            }

            return $next($request);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error en EnsureRoleScope middleware', [
                'user_id' => Auth::id(),
                'route' => $request->route()?->getName(),
                'exception' => $e->getMessage(),
            ]);

            abort(500, 'Error al validar el rol de acceso.');
        }
    }
}