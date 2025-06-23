<?php

namespace Modules\SystemUtility\Database\Seeders;

use Illuminate\Database\Seeder;

class SystemUtilityDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            // StateSeeder::class,
        ]);
    }
}
