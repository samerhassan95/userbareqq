<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get language from Accept-Language header or query parameter
        $locale = $request->header('Accept-Language') 
                  ?? $request->query('lang') 
                  ?? 'en';

        // Normalize locale (remove region codes like en-US -> en)
        $locale = strtolower(substr($locale, 0, 2));

        // Only support 'ar' and 'en'
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
