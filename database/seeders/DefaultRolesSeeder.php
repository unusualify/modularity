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
                'title' => 'Super Admin',
                'name' => 'superadmin',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Admin',
                'name' => 'admin',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Account Manager',
                'name' => 'manager',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Editor',
                'name' => 'editor',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Reporter',
                'name' => 'reporter',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Client Manager',
                'name' => 'client-manager',
                'guard_name' => $modularityAuthGuardName,
            ],
            [
                'title' => 'Client Assistant',
                'name' => 'client-assistant',
                'guard_name' => $modularityAuthGuardName,
            ],
        ]);

    }
}
