<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Facades\Modularity;

class DefaultPermissionsSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $table = config('permission.table_names.permissions');

        Schema::disableForeignKeyConstraints();

        \DB::table($table)->truncate();

        Schema::enableForeignKeyConstraints();

        \DB::table($table)->insert([
            [
                'name' => 'dashboard',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'mediaLibrary',
                'guard_name' => 'unusual_users',
            ],

            ...permissionRecordsFromRoutes([
                'User',
                'Role',
                'Permission',
                // 'PackageContinent',
                // 'PackageRegion',
                // 'PackageCountry',
                // 'PackageDistributionLanguage',
                // 'PackageFeature',
                // 'Package',
                // 'VatRate',
                // 'Currency',
                // 'PriceType',
                // 'Price',
            ], 'unusual_users')
        ]);
    }
}