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
            if (env('AUTO_LOCALE_FINDER', false)) {
                if (mb_strtolower(geoip()->getLocation($request->ip())->iso_code) === 'tr') {
                    $locale = 'tr';
                }
            }
        }

        if($request->has('language')){
            $requestLocale = $request->get('language');
            if(in_array($requestLocale, config('translatable.locales'))){
                $locale = $requestLocale;
            }
        }

        config([modularityBaseKey() . '.locale' => $locale]);
        config([modularityBaseKey() . '.timezone' => auth()->user()->timezone ?? 'Europe/London']);

        App::setLocale($locale);
        App::setFallbackLocale(modularityConfig('fallback_locale'));

        $currency = config('priceable.currency', 'EUR');

        if (! modularityConfig('services.currency_exchange.active')) { // onlyBaseCurrency
            $currency = modularityConfig("payment.locale_currencies.{$locale}", null)
                ?? config('priceable.currency');
        }

        if ($currency !== mb_strtoupper(config('priceable.currency'))) {
            config(['priceable.currency' => $currency]);
            $currencyModel = Currency::where('iso_4217', config('priceable.currency'))->first();
            $request->setUserCurrency($currencyModel);
        }

        config(['priceable.currency_locale' => config('app.locale')]);

        \Carbon\CarbonInterval::setLocale(config('app.locale'));
        \Carbon\Carbon::setLocale(config('app.locale'));

        return $next($request);
    }
}
