<?php

return [
    'prices_are_including_vat' => false,
    'currency' => env('CURRENCY', env('CASHIER_CURRENCY', 'eur')),
    'tables' => [
        'vat_rates' => 'unfy_vat_rates',
        'currencies' => 'unfy_currencies',
        'price_types' => 'unfy_price_types',
        'prices' => 'unfy_prices',
    ],
    'defaults' => [
        'currencies' => 1,
        'vat_rates' => 1,
        'price_type' => 1,
    ],
    'observers' => [
        'price' => \Unusualify\Modularity\Observers\PriceableObserver::class,
    ],
];
