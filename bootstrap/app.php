<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'rls.context' => \App\Http\Middleware\SetSupabaseRLSContext::class,
            'auth.ratelimit' => \App\Http\Middleware\RateLimitAuth::class,
        ]);

        // Registrar middleware RLS en el grupo web DESPUÉS de la sesión y autenticación
        $middleware->web(append: [
            \App\Http\Middleware\SetSupabaseRLSContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Logging de auditoría para AuthorizationException (SIEM-ready)
        $exceptions->report(function (AuthorizationException $e, Request $request) {
            Log::warning('Authorization denied', [
                'user_id' => auth()->id(),
                'user_roles' => auth()->user()?->getRoleNames()->toArray() ?? [],
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ability' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
                'trace_id' => $request->header('X-Request-ID') ?? uniqid('authz_', true),
            ]);
        });
    })->create();
