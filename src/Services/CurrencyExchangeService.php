<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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
     *
     * @return array
     */
    public function fetchExchangeRates(): array
    {
        return Cache::remember('exchange_rates', now()->addHours(1), function () {
            $parameters = [];
            foreach ($this->parameters as $property => $parameter) {
                $parameters[$parameter] = $this->{$property};
            }
            // dd($parameters);
            //https://api.freecurrencyapi.com/v1/latest?apikey={apiKey}&currencies=EUR%2CUSD%2CCAD&base_currency=TRY
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
     *
     * @param float $amount
     * @param string $targetCurrency
     * @return float
     */
    public function convertTo(float $amount, string $targetCurrency): float
    {
        $rates = $this->fetchExchangeRates();

        if (!isset($rates[$targetCurrency])) {
            throw new \Exception("Unsupported currency: {$targetCurrency}");
        }

        return $amount * $rates[$targetCurrency];
    }

    /**
     * Get exchange rate for a specific currency.
     *
     * @param string $currency
     * @return float
     */
    public function getExchangeRate(string $currency): float
    {
        $rates = $this->fetchExchangeRates();

        if (!isset($rates[$currency])) {
            throw new \Exception("Unsupported currency: {$currency}");
        }

        return $rates[$currency];
    }
}
