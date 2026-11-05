<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Roles que tienen acceso al dashboard y módulos del sistema.
     * El rol 'Alumno' se maneja por separado (no tiene dashboard administrativo).
     */
    protected const DASHBOARD_ROLES = [
        'Director',
        'Subdirector',
        'Gestor de Usuarios',
        'Coordinador',
        'Secretaria',
        'Docente Guia',
        'Docente por Asignatura',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si no hay usuario autenticado, dejar que el middleware 'auth' maneje
        if (!$user) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los roles válidos para el dashboard
        $tieneRolValido = $user->roles->pluck('name')->intersect(self::DASHBOARD_ROLES)->isNotEmpty();

        // Si no tiene roles válidos, redirigir a página de "sin rol asignado"
        if (!$tieneRolValido) {
            // Permitir acceso a rutas de perfil y logout para que pueda cerrar sesión
            $rutasPermitidasSinRol = [
                'profile.edit',
                'profile.update',
                'profile.destroy',
                'logout',
                'sin-rol', // la propia ruta de sin-rol
            ];

            $rutaActual = $request->route()?->getName();

            if (!in_array($rutaActual, $rutasPermitidasSinRol)) {
                return redirect()->route('sin-rol')
                    ->with('warning', 'Tu cuenta no tiene ningún rol asignado. Contacta al administrador para que te asigne los permisos correspondientes.');
            }
        }

        return $next($request);
    }
}