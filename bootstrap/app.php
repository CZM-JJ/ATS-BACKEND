<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies for Laravel Cloud - this is crucial for X-Forwarded-* headers
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        // Add security headers
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Register custom middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'perm' => \App\Http\Middleware\CheckPermission::class,
            'auth.cookie' => \App\Http\Middleware\AttachAuthTokenFromCookie::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle production exceptions gracefully
        $exceptions->render(function (Exception $e) {
            // Log all exceptions
            if (!($e instanceof \Illuminate\Auth\AuthenticationException) 
                && !($e instanceof \Illuminate\Auth\Access\AuthorizationException)
                && !($e instanceof \Illuminate\Validation\ValidationException)) {
                \Illuminate\Support\Facades\Log::error('Unhandled exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'path' => request()->path(),
                    'method' => request()->method(),
                ]);
            }
        });
    })->create();
