<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array fetchExchangeRates()
 * @method static float convertTo(float $amount, string $targetCurrency)
 * @method static float getExchangeRate(string $currency)
 *
 * @see \Unusualify\Modularity\Services\CurrencyExchangeService
 */
class CurrencyExchange extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'currency.exchange';
    }
}
