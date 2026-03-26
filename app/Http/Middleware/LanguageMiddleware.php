<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, ['en', 'es'])) {
                Session::put('app_locale', $lang);
            }
        }

        $locale = Session::get('app_locale', config('app.locale'));
        App::setLocale($locale);

        return $next($request);
    }
}
