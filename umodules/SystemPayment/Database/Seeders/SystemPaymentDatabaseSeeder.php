<?php

namespace Modules\SystemPayment\Database\Seeders;

use Illuminate\Database\Seeder;

class SystemPaymentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PaymentServiceSeeder::class
        ]);
    }
}
