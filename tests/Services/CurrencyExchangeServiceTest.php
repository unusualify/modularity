<?php

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularous\Services\CurrencyExchangeService;
use Unusualify\Modularous\Tests\TestCase;

class CurrencyExchangeServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.services.currency_exchange.endpoint', 'http://api.test/latest');
        Config::set('modularous.services.currency_exchange.api_key', 'test-key');
        Config::set('modularous.services.currency_exchange.parameters', ['apiKey' => 'apikey', 'baseCurrency' => 'base_currency']);
        Config::set('modularous.services.currency_exchange.rates_key', 'data');

        $this->service = new CurrencyExchangeService;
    }

    /** @test */
    public function it_can_fetch_and_cache_rates()
    {
        Http::fake([
            'api.test/*' => Http::response(['data' => ['USD' => 1.1, 'EUR' => 1.0]], 200),
        ]);

        $rates = $this->service->fetchExchangeRates();

        $this->assertEquals(1.1, $rates['USD']);
        $this->assertTrue(Cache::has('exchange_rates'));
    }

    /** @test */
    public function it_can_convert_amount()
    {
        Cache::put('exchange_rates', ['USD' => 1.1, 'EUR' => 1.0], 3600);

        $converted = $this->service->convertTo(100, 'USD');
        $this->assertEquals(110.0, $converted);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_currency()
    {
        Cache::put('exchange_rates', ['USD' => 1.1], 3600);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unsupported currency: EUR');

        $this->service->convertTo(100, 'EUR');
    }

    /** @test */
    public function it_can_get_exchange_rate()
    {
        Cache::put('exchange_rates', ['USD' => 1.1], 3600);

        $rate = $this->service->getExchangeRate('USD');
        $this->assertEquals(1.1, $rate);
    }

    /** @test */
    public function it_throws_when_fetch_fails(): void
    {
        Http::fake([
            'api.test/*' => Http::response(['error' => 'down'], 500),
        ]);

        Cache::forget('exchange_rates');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to fetch exchange rates');

        $this->service->fetchExchangeRates();
    }

    /** @test */
    public function it_uses_ceil_rounding_for_exchange_rate_lookup(): void
    {
        Cache::put('exchange_rates', ['USD' => 1.115], 3600);

        $this->assertSame(2.0, $this->service->getExchangeRate('USD', 0, 'ceil'));
    }
}
