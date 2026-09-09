<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Roles allowed to access the admin panel.
     */
    public const ROLES = ['admin', 'manager'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, self::ROLES, true)) {
            return redirect()->route('login')->with('error', 'You need an admin account to access the dashboard.');
        }

        return $next($request);
    }
}
