<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Unusualify\Modularity\Facades\Modularity;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $table = config('permission.table_names.roles');

        // DB::table($table)->truncate();
        $modularityAuthGuardName = Modularity::getAuthGuardName();
        DB::table($table)->insert([
            [
                'name' => 'superadmin',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'admin',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'manager',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'editor',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'reporter',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'client-manager',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'name' => 'client-assistant',
                'guard_name' => $modularityAuthGuardName,
            ],
        ]);

    }
}
