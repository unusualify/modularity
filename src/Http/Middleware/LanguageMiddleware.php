<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use JoeDixon\Translation\TranslationManager;
use Torann\GeoIP\Facades\GeoIP;
use JoeDixon\Translation\Drivers\Translation;
use JoeDixon\Translation\Scanner;

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
        App::setFallbackLocale(config(unusualBaseKey() . '.fallback_locale'));

        return $next($request);
    }
}
