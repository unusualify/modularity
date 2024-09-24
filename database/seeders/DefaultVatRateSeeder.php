<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class DefaultVatRateSeeder extends Seeder{

    public function run(){

        $table = config('priceable.tables.vat_rates');
        $seedArray = [
            [
                'name' => 'TR-Standard VAT',
                'slug' => 'turkey-standard-vat',
                'rate' => 20.00,
            ],
            [
                'name' => 'TR-Reduced VAT',
                'slug' => 'turkey-reduced-vat',
                'rate' => 10.00,
            ],
            [
                'name' => 'TR-Zero VAT',
                'slug' => 'turkey-zero-vat',
                'rate' => 0.00,
            ],
            [
                'name' => 'DE-Standard VAT',
                'slug' => 'germany-standard-vat',
                'rate' => 19.00,
            ],
            [
                'name' => 'DE-Reduced VAT',
                'slug' => 'germany-reduced-vat',
                'rate' => 7.00,
            ],
            [
                'name' => 'FR-Standard VAT',
                'slug' => 'france-standard-vat',
                'rate' => 20.00,
            ],
            [
                'name' => 'FR-Reduced VAT',
                'slug' => 'france-reduced-vat',
                'rate' => 5.50,
            ],
            [
                'name' => 'IT-Standard VAT',
                'slug' => 'italy-standard-vat',
                'rate' => 22.00,
            ],
            [
                'name' => 'IT-Reduced VAT',
                'slug' => 'italy-reduced-vat',
                'rate' => 10.00,
            ],
            [
                'name' => 'ES-Standard VAT',
                'slug' => 'spain-standard-vat',
                'rate' => 21.00,
            ],
            [
                'name' => 'ES-Reduced VAT',
                'slug' => 'spain-reduced-vat',
                'rate' => 10.00,
            ],
            [
                'name' => 'NL-Standard VAT',
                'slug' => 'netherlands-standard-vat',
                'rate' => 21.00,
            ],
            [
                'name' => 'NL-Reduced VAT',
                'slug' => 'netherlands-reduced-vat',
                'rate' => 9.00,
            ],
            // Add more countries and VAT rates as needed
        ];
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $vatRates = array_map(fn($vatRate) => $vatRate += ['created_at' => $now], $seedArray);
        DB::table($table)->insert($vatRates);
    }
}

