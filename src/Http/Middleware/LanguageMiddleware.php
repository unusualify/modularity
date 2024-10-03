<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Oobook\Priceable\Models\Currency;

class LanguageMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = config('app.locale');

        if ($request->user() && $request->user()->language) {
            $locale = $request->user()->language;
        } else {
            if (mb_strtolower(geoip()->getLocation($request->ip())->iso_code) === 'tr') {
                $locale = 'tr';
            }
        }

        config([unusualBaseKey() . '.locale' => $locale]);
        config([unusualBaseKey() . '.timezone' => auth()->user()->timezone ?? 'Europe/London']);
        App::setLocale($locale);
        App::setFallbackLocale(unusualConfig('fallback_locale'));

        $currency = unusualConfig("payment.locale_currencies.{$locale}", null)
            ?? config('priceable.currency');

        if($currency !== mb_strtoupper(config('priceable.currency'))){
            config(['priceable.currency' => $currency]);
            $currencyModel = Currency::where('iso_4217', config('priceable.currency'))->first();
            $request->setUserCurrency($currencyModel);
        }

        \Carbon\CarbonInterval::setLocale(config('app.locale'));
        \Carbon\Carbon::setLocale(config('app.locale'));

        return $next($request);
    }
}
