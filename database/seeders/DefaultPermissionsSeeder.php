<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        DB::table($table)->truncate();

        Schema::enableForeignKeyConstraints();

        DB::table($table)->insert([
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
                // 'PackageLanguage',
                // 'PackageFeature',
                // 'Package',
                // 'VatRate',
                // 'Currency',
                // 'PriceType',
                // 'Price',
            ], 'unusual_users'),
        ]);
    }
}
