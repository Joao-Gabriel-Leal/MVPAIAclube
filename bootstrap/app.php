<?php

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasAnyRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (SymfonyResponse $response, \Throwable $exception, Request $request) {
            if ($response->getStatusCode() !== 419) {
                return $response;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sua sessao expirou. Atualize a pagina e tente novamente.',
                ], 419);
            }

            $fallback = auth()->check()
                ? route('dashboard', absolute: false)
                : route('login', absolute: false);

            return redirect()
                ->to($request->headers->get('referer') ?: $fallback)
                ->with('status', 'Sua sessao expirou. Atualize a pagina e tente novamente.');
        });
    })->create();
