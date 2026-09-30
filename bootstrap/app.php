<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable database connection fallback for all web requests
        $middleware->append(\App\Http\Middleware\EnsureDatabaseConnection::class);

        // Apply the visitor's chosen language (session) to every web request
        $middleware->append(\App\Http\Middleware\SetLocale::class);

        // Role-based aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

/*
 * Database fallback — web, console AND scheduler.
 *
 * The web middleware (EnsureDatabaseConnection) only covers HTTP requests.
 * Scheduled commands (scrape:run, invoices:send-overdue-reminders, queue:work)
 * boot without that middleware, so when MySQL is down they crashed with a
 * connection error and the 24/7 auto-scraper silently did nothing.
 *
 * The `booted` callback runs after env/config are loaded, in every context.
 * If MySQL is configured but unreachable, flip the default connection to the
 * local SQLite file so everything keeps working.
 */
$app->booted(function ($app) {
    if (env('DB_FALLBACK_TO_SQLITE', false) && config('database.default') === 'mysql') {
        try {
            $app['db']->connection()->getPdo();
        } catch (\Throwable $e) {
            if (file_exists(database_path('database.sqlite'))) {
                $app['db']->setDefaultConnection('sqlite');

                // Database-backed session store follows the connection
                config()->set('session.driver', 'file');
            }
        }
    }
});

return $app;
