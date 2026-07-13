<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Currency;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPricing\Entities\Currency;
use Unusualify\Modularous\Services\Currency\SystemPricingCurrencyProvider;
use Unusualify\Modularous\Tests\TestCase;

class SystemPricingCurrencyProviderTest extends TestCase
{
    private SystemPricingCurrencyProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(Currency::class)) {
            $this->markTestSkipped('SystemPricing Currency model is not available.');
        }

        Schema::dropIfExists('currencies');
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('symbol')->nullable();
            $table->string('iso_4217', 3)->nullable();
            $table->integer('iso_4217_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Cache::flush();
        $this->provider = new SystemPricingCurrencyProvider;
    }

    public function test_is_available_when_currency_model_exists(): void
    {
        $this->assertTrue($this->provider->isAvailable());
    }

    public function test_find_by_iso_4217_returns_matching_currency(): void
    {
        $currency = Currency::query()->create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'iso_4217' => 'EUR',
            'iso_4217_number' => 978,
        ]);

        $found = $this->provider->findByIso4217('eur');

        $this->assertNotNull($found);
        $this->assertSame($currency->id, $found->id);
    }

    public function test_find_by_id_returns_matching_currency(): void
    {
        $currency = Currency::query()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'iso_4217' => 'USD',
            'iso_4217_number' => 840,
        ]);

        $found = $this->provider->findById($currency->id);

        $this->assertNotNull($found);
        $this->assertSame('USD', $found->iso_4217);
    }

    public function test_get_currencies_for_select_returns_enabled_rows(): void
    {
        Currency::query()->create([
            'name' => 'Turkish Lira',
            'symbol' => 'TRY',
            'iso_4217' => 'TRY',
            'iso_4217_number' => 949,
        ]);

        config()->set('modularous.services.currency_exchange.active', false);

        $options = $this->provider->getCurrenciesForSelect();

        $this->assertNotEmpty($options);
        $this->assertSame('TRY', $options[0]['iso'] ?? null);
    }
}
