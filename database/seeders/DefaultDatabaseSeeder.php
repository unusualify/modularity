<?php

namespace Unusualify\Modularity\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\SystemPayment\Database\Seeders\SystemPaymentDatabaseSeeder;

class DefaultDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DefaultRolesSeeder::class,
            DefaultPermissionsSeeder::class,
            DefaultCurrencySeeder::class,
            DefaultVatRateSeeder::class,
            DefaultPriceTypeSeeder::class,
            DefaultCountrySeeder::class,
            SystemPaymentDatabaseSeeder::class,
        ]);
    }
}
