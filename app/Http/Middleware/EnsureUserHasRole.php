<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    private const ALLOWED_ROLES = [
        'directora',
        'subdirectora',
        'secretaria',
        'coordinador',
        'docente_guia',
        'docente_asignatura',
        'docente_auxiliar',
        'docente_suplente',
        'psicologa',
        'gestor_it',
        'Director',
        'Subdirector',
        'Gestor de Usuarios',
        'Coordinador',
        'Secretaria',
        'Docente Guia',
        'Docente Guía',
        'Docente por Asignatura',
        'Docente Auxiliar',
        'Docente Suplente',
        'Psicologa',
        'Psicóloga',
    ];

    private const BYPASS_ROUTES = [
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'logout',
        'sin-rol',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();

            if (!$user instanceof Usuario) {
                return $next($request);
            }

            if (isset($user->activo) && !(bool) $user->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu cuenta se encuentra desactivada. Contacta a la administración.']);
            }

            $currentRoute = $request->route()?->getName();
            if ($currentRoute && in_array($currentRoute, self::BYPASS_ROUTES, true)) {
                return $next($request);
            }

            $hasAllowedRole = $user->roles()->pluck('name')->intersect(self::ALLOWED_ROLES)->isNotEmpty();

            if (!$hasAllowedRole) {
                return redirect()->route('sin-rol')
                    ->with('warning', 'Tu cuenta no tiene un rol operativo asignado. Contacta al administrador.');
            }

            return $next($request);
        } catch (\Throwable $e) {
            Log::error('Error en EnsureUserHasRole middleware', [
                'user_id' => Auth::id(),
                'route' => $request->route()?->getName(),
                'exception' => $e->getMessage(),
            ]);

            abort(500, 'No fue posible verificar los permisos de acceso.');
        }
    }
}