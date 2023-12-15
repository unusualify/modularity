<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SpRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $table = config('permission.table_names.roles');

        \DB::table($table)->truncate();

        \DB::table($table)->insert([
            [
                'name' => 'superadmin',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'admin',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'manager',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'editor',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'reporter',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'client-manager',
                'guard_name' => 'unusual_users',
            ],
            [
                'name' => 'client-assistant',
                'guard_name' => 'unusual_users',
            ],
        ]);
    }
}
