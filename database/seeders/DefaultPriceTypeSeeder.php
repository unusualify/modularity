<?php

namespace Unusualify\Modularity\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPriceTypeSeeder extends Seeder{

    public function run(){
        $table = config('priceable.tables.price_types');
        DB::table($table)->insert([
            [
                'name' => 'Default Price Type',
                'slug' => 'default-price-type',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
