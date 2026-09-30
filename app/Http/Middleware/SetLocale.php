<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales — keep in sync with resources/views/layouts/public.blade.php switcher.
     */
    public const SUPPORTED = ['en', 'sw'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'en');

        return $next($request);
    }
}
