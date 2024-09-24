<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class LanguageMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = config('app.locale');

        if ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        }else{
            if( strtolower( geoip()->getLocation($request->ip())->iso_code) === 'tr') {
                $locale = 'tr';
            }
        }


        config([unusualBaseKey() . '.locale' => $locale]);
        config([unusualBaseKey() . '.timezone' => auth()->user()->timezone ?? 'Europe/London']);
        App::setLocale($locale);
        App::setFallbackLocale(unusualConfig('fallback_locale'));

        \Carbon\CarbonInterval::setLocale(config('app.locale'));
        \Carbon\Carbon::setLocale(config('app.locale'));

        return $next($request);
    }
}
