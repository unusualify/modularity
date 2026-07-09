<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Mockery;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Http\Middleware\LanguageMiddleware;
use Unusualify\Modularous\Tests\TestCase;

class LanguageMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.available_user_locales', ['en', 'tr', 'nl']);
        Config::set('modularous.fallback_locale', 'en');
        Config::set('modularous.services.currency_exchange.active', false);
        Config::set('modularous.payment.locale_currencies.tr', 'TRY');
        Config::set('priceable.currency', 'EUR');
    }

    public function test_uses_language_query_parameter(): void
    {
        $middleware = new LanguageMiddleware;
        $request = Request::create('/?language=en', 'GET');

        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('en', App::getLocale());
        $this->assertSame('EUR', config('priceable.currency'));
    }

    public function test_uses_authenticated_user_language(): void
    {
        $user = new User;
        $user->language = 'nl';
        $user->timezone = 'Europe/Amsterdam';

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);
        Auth::shouldReceive('user')->andReturn($user);

        $middleware = new LanguageMiddleware;
        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('nl', App::getLocale());
        $this->assertSame('Europe/Amsterdam', config(modularousBaseKey() . '.timezone'));
    }

    public function test_falls_back_for_translation_index_route(): void
    {
        Route::get('/translations', fn () => 'translations')->name('languages.translations.index');

        Route::shouldReceive('currentRouteName')->andReturn('languages.translations.index');

        $middleware = new LanguageMiddleware;
        $request = Request::create('/translations?language=tr', 'GET');

        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('en', App::getLocale());
    }

    public function test_sets_user_currency_when_provider_available(): void
    {
        Config::set('modularous.payment.locale_currencies.tr', 'TRY');
        Config::set('priceable.currency', 'EUR');

        $currencyModel = (object) ['id' => 99, 'iso_4217' => 'TRY'];

        $provider = Mockery::mock(CurrencyProviderInterface::class);
        $provider->shouldReceive('isAvailable')->andReturn(true);
        $provider->shouldReceive('findByIso4217')->with('TRY')->andReturn($currencyModel);
        $this->app->instance(CurrencyProviderInterface::class, $provider);

        $request = new class extends Request
        {
            public ?object $userCurrency = null;

            public function setUserCurrency($currency): void
            {
                $this->userCurrency = $currency;
            }
        };
        $request->initialize(['language' => 'tr'], [], [], [], [], ['REQUEST_URI' => '/?language=tr']);

        $middleware = new LanguageMiddleware;
        $middleware->handle($request, fn () => response('ok'));

        $this->assertSame($currencyModel, $request->userCurrency);
        $this->assertSame('TRY', config('priceable.currency'));
    }
}
