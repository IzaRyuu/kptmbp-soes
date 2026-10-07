<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies for Render HTTPS termination
        $middleware->trustProxies(at: '*');

        // Register the RBAC role middleware alias
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->respond(function ($response, Throwable $exception, Request $request) {
            if ($exception instanceof TokenMismatchException) {
                return redirect()->route('login')->with('error', 'Your session expired. Please log in again.');
            }
            return $response;
        });
    })->create();
