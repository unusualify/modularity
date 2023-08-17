<?php

namespace OoBook\CRM\Base\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Torann\GeoIP\Facades\GeoIP;

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
        $locale = 'tr';

        if ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
            // config([unusualBaseKey() . '.locale' => $request->user()->language]);
        }else{

            // config([unusualBaseKey() . '.locale' => 'tr']);

            if( strtolower( geoip()->getLocation($request->ip())->iso_code) === 'tr') {
                $locale = 'tr';
            }
            // dd(
            //     // $_SERVER['REMOTE_ADDR'],
            //     // request()->getClientIp(true),
            //     // $request->ip(),
            //     // get_class_methods(\Torann\GeoIP\Facades\GeoIP::class),
            //     geoip()->getLocation($request->ip())->iso_code,
            //     // geoip()->getLocation('176.236.116.66'),
            //     $_SERVER
            // );
        }

        // config([
        //     'app.locale' => config(unusualBaseKey() . '.locale'),
        //     'app.fallback_locale' => config(unusualBaseKey() . '.fallback_locale')
        // ]);
        config([unusualBaseKey() . '.locale' => $locale]);
        App::setLocale($locale);
        App::setFallbackLocale(config(unusualBaseKey() . '.fallback_locale'));

        return $next($request);
    }
}
