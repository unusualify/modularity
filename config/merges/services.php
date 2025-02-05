<?php

return [
    'currency_exchange' => [
        'active' => env('CURRENCY_EXCHANGE_ACTIVE', true),
        'api_key' => env('CURRENCY_EXCHANGE_API_KEY'),
        'base_currency' => 'EUR',
        'endpoint' => 'https://api.freecurrencyapi.com/v1/latest', // 'https://openexchangerates.org/api/latest.json',
        'parameters' => [
            'apiKey' => 'apikey', // app_id for openexchangerates
            'baseCurrency' => 'base_currency', // base for openexchangerates
        ],
        'rates_key' => 'data', // rates for openexchangerates
    ],
];
