<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Currency;

use Unusualify\Modularous\Services\Currency\NullCurrencyProvider;
use Unusualify\Modularous\Tests\TestCase;

class NullCurrencyProviderTest extends TestCase
{
    public function test_returns_null_and_empty_values(): void
    {
        $provider = new NullCurrencyProvider;

        $this->assertNull($provider->findByIso4217('EUR'));
        $this->assertSame([], $provider->getCurrenciesForSelect());
        $this->assertFalse($provider->isAvailable());
    }
}
