<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\NormalizeSessionState::class,
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->alias([
            'check.terms' => \App\Http\Middleware\CheckTerms::class,
            'check.role' => \App\Http\Middleware\CheckRole::class,
            'check.active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'verified.except_admin' => \App\Http\Middleware\EnsureEmailIsVerifiedExceptAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
