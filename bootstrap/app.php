<?php

use App\Http\Middleware\EnsureAnioEscolarModificable;
use App\Http\Middleware\EnsureRoleScope;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RateLimitAuth;
use App\Http\Middleware\SetSupabaseRLSContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'rol.acceso' => EnsureRoleScope::class,
            'anio.modificable' => EnsureAnioEscolarModificable::class,
            'rls.context' => SetSupabaseRLSContext::class,
            'auth.ratelimit' => RateLimitAuth::class,
        ]);

        $middleware->web(append: [
            SetSupabaseRLSContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->report(function (AuthorizationException $e, Request $request) {
            try {
                $user = auth()->user();
                Log::warning('Authorization denied', [
                    'user_id' => $user?->id,
                    'user_roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [],
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_agent' => $request->userAgent(),
                    'ability' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                    'trace_id' => $request->header('X-Request-ID') ?? uniqid('authz_', true),
                ]);
            } catch (\Throwable $logError) {
                Log::error('Error al registrar AuthorizationException', [
                    'exception' => $logError->getMessage(),
                ]);
            }
        });
    })->create();