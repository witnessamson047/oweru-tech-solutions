<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verify database connection is working
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // If connection fails, try SQLite fallback
            $sqlitePath = database_path('database.sqlite');
            if (file_exists($sqlitePath)) {
                DB::setDefaultConnection('sqlite');
                DB::connection('sqlite')->getPdo();
            } else {
                throw $e;
            }
        }

        return $next($request);
    }
}
