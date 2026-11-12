<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    /**
     * Handle an incoming request.
     * Rate limiting para endpoints de autenticación sensibles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        
        // Login: 5 intentos por minuto por IP
        if ($request->is('login') && $request->isMethod('post')) {
            $key = 'auth:login:' . $ip;
            
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return response()->json([
                    'message' => "Demasiados intentos de inicio de sesión. Intente en {$seconds} segundos."
                ], 429);
            }
            
            RateLimiter::hit($key, 60);
        }

        // Password reset request: 3 intentos por hora por IP
        if ($request->is('forgot-password') && $request->isMethod('post')) {
            $key = 'auth:password_reset:' . $ip;
            
            if (RateLimiter::tooManyAttempts($key, 3)) {
                return back()->with('error', 'Demasiadas solicitudes de restablecimiento. Intente más tarde.');
            }
            
            RateLimiter::hit($key, 3600);
        }

        // Email verification: 6 intentos por minuto (ya existe en routes)
        if ($request->is('email/verification-notification') && $request->isMethod('post')) {
            $key = 'auth:verify_email:' . $ip;
            
            if (RateLimiter::tooManyAttempts($key, 6)) {
                return back()->with('error', 'Demasiadas solicitudes. Intente en un minuto.');
            }
            
            RateLimiter::hit($key, 60);
        }

        return $next($request);
    }
}