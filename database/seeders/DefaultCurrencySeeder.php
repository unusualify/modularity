<?php

namespace Unusualify\Modularity\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCurrencySeeder extends Seeder
{
    public function run()
    {
        $table = config('priceable.tables.currencies');

        $seedArray = [
            [
                'name' => 'Euro',
                'symbol' => '€',
                'iso_4217' => 'EUR',
                'iso_4217_code' => 978,
            ],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'iso_4217' => 'USD',
                'iso_4217_code' => 840,
            ],
            [
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'iso_4217' => 'TRY',
                'iso_4217_code' => 949,
            ],
            [
                'name' => 'British Pound',
                'symbol' => '£',
                'iso_4217' => 'GBP',
                'iso_4217_code' => 826,
            ],
            [
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'iso_4217' => 'JPY',
                'iso_4217_code' => 392,
            ],
            [
                'name' => 'Canadian Dollar',
                'symbol' => 'CA$',
                'iso_4217' => 'CAD',
                'iso_4217_code' => 124,
            ],
            [
                'name' => 'Australian Dollar',
                'symbol' => 'A$',
                'iso_4217' => 'AUD',
                'iso_4217_code' => 036,
            ],
            [
                'name' => 'Swiss Franc',
                'symbol' => 'CHF',
                'iso_4217' => 'CHF',
                'iso_4217_code' => 756,
            ],
            [
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'iso_4217' => 'CNY',
                'iso_4217_code' => 156,
            ],
            [
                'name' => 'Swedish Krona',
                'symbol' => 'kr',
                'iso_4217' => 'SEK',
                'iso_4217_code' => 752,
            ],
            [
                'name' => 'New Zealand Dollar',
                'symbol' => 'NZ$',
                'iso_4217' => 'NZD',
                'iso_4217_code' => 554,
            ],
            [
                'name' => 'Mexican Peso',
                'symbol' => 'MX$',
                'iso_4217' => 'MXN',
                'iso_4217_code' => 484,
            ],
            [
                'name' => 'Singapore Dollar',
                'symbol' => 'S$',
                'iso_4217' => 'SGD',
                'iso_4217_code' => 702,
            ],
            [
                'name' => 'Hong Kong Dollar',
                'symbol' => 'HK$',
                'iso_4217' => 'HKD',
                'iso_4217_code' => 344,
            ],
            [
                'name' => 'Norwegian Krone',
                'symbol' => 'kr',
                'iso_4217' => 'NOK',
                'iso_4217_code' => 578,
            ],
            [
                'name' => 'South Korean Won',
                'symbol' => '₩',
                'iso_4217' => 'KRW',
                'iso_4217_code' => 410,
            ],
            [
                'name' => 'Brazilian Real',
                'symbol' => 'R$',
                'iso_4217' => 'BRL',
                'iso_4217_code' => 986,
            ],
            [
                'name' => 'Russian Ruble',
                'symbol' => '₽',
                'iso_4217' => 'RUB',
                'iso_4217_code' => 643,
            ],
            [
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'iso_4217' => 'INR',
                'iso_4217_code' => 356,
            ],
            [
                'name' => 'South African Rand',
                'symbol' => 'R',
                'iso_4217' => 'ZAR',
                'iso_4217_code' => 710,
            ],
        ];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $currencyTypes = array_map(fn ($currencyType) => $currencyType += ['created_at' => $now], $seedArray);
        DB::table($table)->insert($currencyTypes);
    }
}
