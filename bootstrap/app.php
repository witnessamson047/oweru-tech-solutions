<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable database connection fallback for all web requests
        $middleware->append(\App\Http\Middleware\EnsureDatabaseConnection::class);

        // Role-based aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

// Fallback to file sessions if database is unavailable
if (env('DB_FALLBACK_TO_SQLITE', false) && env('DB_CONNECTION') === 'mysql') {
    Config::set('session.driver', 'file');
}
