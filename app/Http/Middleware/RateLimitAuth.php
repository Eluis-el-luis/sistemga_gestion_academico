<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $ip = (string) $request->ip();

            if ($request->is('login') && $request->isMethod('post')) {
                $emailKey = strtolower(trim((string) $request->input('email', '')));
                $key = 'auth:login:' . sha1($ip . '|' . $emailKey);

                if (RateLimiter::tooManyAttempts($key, 5)) {
                    $seconds = RateLimiter::availableIn($key);

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => "Demasiados intentos de inicio de sesión. Intente en {$seconds} segundos.",
                        ], 429);
                    }

                    return back()
                        ->withInput($request->except('password'))
                        ->withErrors([
                            'email' => "Demasiados intentos de inicio de sesión. Intente en {$seconds} segundos.",
                        ]);
                }

                RateLimiter::hit($key, 60);
            }

            if ($request->is('forgot-password') && $request->isMethod('post')) {
                $key = 'auth:password_reset:' . sha1($ip);

                if (RateLimiter::tooManyAttempts($key, 3)) {
                    return back()->with('error', 'Demasiadas solicitudes de restablecimiento. Intente más tarde.');
                }

                RateLimiter::hit($key, 3600);
            }

            if ($request->is('email/verification-notification') && $request->isMethod('post')) {
                $key = 'auth:verify_email:' . sha1($ip);

                if (RateLimiter::tooManyAttempts($key, 6)) {
                    return back()->with('error', 'Demasiadas solicitudes. Intente en un minuto.');
                }

                RateLimiter::hit($key, 60);
            }

            return $next($request);
        } catch (\Throwable $e) {
            Log::error('Error en RateLimitAuth middleware', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'exception' => $e->getMessage(),
            ]);

            return $next($request);
        }
    }
}