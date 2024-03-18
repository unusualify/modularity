<?php

namespace Unusualify\Modularity\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultCurrencySeeder extends Seeder
{
    public function run(){
        $table = config('priceable.tables.currencies');

        $seedArray = [
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'iso_4217' => 'USD',
            ],
            [
                'name' => 'Euro',
                'symbol' => '€',
                'iso_4217' => 'EUR',
            ],
            [
                'name' => 'British Pound',
                'symbol' => '£',
                'iso_4217' => 'GBP',
            ],
            [
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'iso_4217' => 'JPY',
            ],
            [
                'name' => 'Canadian Dollar',
                'symbol' => 'CA$',
                'iso_4217' => 'CAD',
            ],
            [
                'name' => 'Australian Dollar',
                'symbol' => 'A$',
                'iso_4217' => 'AUD',
            ],
            [
                'name' => 'Swiss Franc',
                'symbol' => 'CHF',
                'iso_4217' => 'CHF',
            ],
            [
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'iso_4217' => 'CNY',
            ],
            [
                'name' => 'Swedish Krona',
                'symbol' => 'kr',
                'iso_4217' => 'SEK',
            ],
            [
                'name' => 'New Zealand Dollar',
                'symbol' => 'NZ$',
                'iso_4217' => 'NZD',
            ],
            [
                'name' => 'Mexican Peso',
                'symbol' => 'MX$',
                'iso_4217' => 'MXN',
            ],
            [
                'name' => 'Singapore Dollar',
                'symbol' => 'S$',
                'iso_4217' => 'SGD',
            ],
            [
                'name' => 'Hong Kong Dollar',
                'symbol' => 'HK$',
                'iso_4217' => 'HKD',
            ],
            [
                'name' => 'Norwegian Krone',
                'symbol' => 'kr',
                'iso_4217' => 'NOK',
            ],
            [
                'name' => 'South Korean Won',
                'symbol' => '₩',
                'iso_4217' => 'KRW',
            ],
            [
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'iso_4217' => 'TRY',
            ],
            [
                'name' => 'Brazilian Real',
                'symbol' => 'R$',
                'iso_4217' => 'BRL',
            ],
            [
                'name' => 'Russian Ruble',
                'symbol' => '₽',
                'iso_4217' => 'RUB',
            ],
            [
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'iso_4217' => 'INR',
            ],
            [
                'name' => 'South African Rand',
                'symbol' => 'R',
                'iso_4217' => 'ZAR',
            ],
        ];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $currencyTypes = array_map(fn($currencyType)=> $currencyType += ['created_at' =>$now], $seedArray);
        DB::table($table)->insert($currencyTypes);
    }

}
