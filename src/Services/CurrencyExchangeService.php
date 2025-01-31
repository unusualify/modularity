<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyExchangeService
{
    protected $apiKey;

    protected $baseCurrency;

    protected $endpoint;

    protected $parameters;

    protected $ratesKey;

    public function __construct()
    {
        // $this->endpoint = 'https://openexchangerates.org/api/latest.json';
        $this->endpoint = config('modularity.services.currency_exchange.endpoint');
        $this->apiKey = config('modularity.services.currency_exchange.api_key');
        $this->baseCurrency = config('modularity.services.currency_exchange.base_currency', 'EUR');
        $this->parameters = config('modularity.services.currency_exchange.parameters');
        $this->ratesKey = config('modularity.services.currency_exchange.rates_key');
    }

    /**
     * Fetch and cache exchange rates.
     */
    public function fetchExchangeRates(): array
    {
        return Cache::remember('exchange_rates', now()->addHours(1), function () {
            $parameters = [];
            foreach ($this->parameters as $property => $parameter) {
                $parameters[$parameter] = $this->{$property};
            }
            // dd($parameters);
            // https://api.freecurrencyapi.com/v1/latest?apikey={apiKey}&currencies=EUR%2CUSD%2CCAD&base_currency=TRY
            $response = Http::get($this->endpoint, $parameters);
            // $response = Http::get($this->endpoint, [
            //     'app_id' => $this->apiKey,
            //     'base' => $this->baseCurrency,
            // ]);

            if ($response->successful()) {
                return $response->json($this->ratesKey);
            }

            throw new \Exception('Failed to fetch exchange rates');
        });
    }

    /**
     * Convert amount from base currency to target currency.
     * @param float $amount Amount to convert
     * @param string $targetCurrency Currency to convert to
     * @param int $decimals Number of decimal places (default: 2)
     */
    public function convertTo(float $amount, string $targetCurrency, int $decimals = 2, string $round = 'round'): float
    {
        $rates = $this->fetchExchangeRates();

        if (! isset($rates[$targetCurrency])) {
            throw new \Exception("Unsupported currency: {$targetCurrency}");
        }

        if ($round == 'ceil') {
            return ceil($amount * $rates[$targetCurrency]);
        }
        if ($round == 'floor') {
            return floor($amount * $rates[$targetCurrency]);
        }

        return round($amount * $rates[$targetCurrency], $decimals);
    }

    /**
     * Get exchange rate for a specific currency.
     */
    public function getExchangeRate(string $currency): float
    {
        $rates = $this->fetchExchangeRates();

        if (! isset($rates[$currency])) {
            throw new \Exception("Unsupported currency: {$currency}");
        }

        return $rates[$currency];
    }
}
