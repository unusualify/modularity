<?php

namespace Unusualify\Modularity\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $table = modularityConfig('tables.users');

        Schema::disableForeignKeyConstraints();

        DB::table($table)->truncate();

        Schema::enableForeignKeyConstraints();

        DB::table($table)->insert([
            [
                'id' => 1,
                'name' => 'unusualify',
                'company_id' => null,
                'surname' => null,
                'job_title' => null,
                'email' => 'software-dev@unusualgrowth.com',
                'language' => 'en',
                'timezone' => 'Europe/London',
                'password' => '$2y$10$./.YS6k9clh6IU1cOdJLcuqqnY1FkxzjAcJ5Wv8qoQj6nYGV.fjqy',
            ],
        ]);

    }
}
