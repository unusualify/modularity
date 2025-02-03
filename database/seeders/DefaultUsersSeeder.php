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
            [
                'id' => 2,
                'name' => 'B2press',
                'company_id' => null,
                'surname' => null,
                'job_title' => null,
                'email' => 'info@b2press.com',
                'language' => 'en',
                'timezone' => 'Europe/London',
                'password' => '$2y$10$ldyuNCY./iLsXQRxslQs0ex8Bryf3VAVDK3.826plGqjhGqAgPqwS',
            ],
            // [
            //     'id' => 5,
            //     'name' => 'Oğuzhan',
            //     'company_id' => 1,
            //     'surname' => 'Bükçüoğlu',
            //     'job_title' => 'Senior',
            //     'email' => 'oguz.bukcuoglu@gmail.com',
            //     'language' => 'English',
            //     'timezone' => 'Europe/Istanbul',
            //     'phone' => '+90 552 313 08 93',
            //     'country' => 'Türkiye',
            //     'email_verified_at' => NULL,
            //     'password' => '$2y$10$NxJoxJ0bN3HheQG4mENCFuUuKbKQ0WV6Rh5s7VZUDB/KYSleZZqGm',
            //     'remember_token' => NULL,
            //     'created_at' => '2023-11-30 07:32:10',
            //     'updated_at' => '2023-11-30 11:49:47',
            //     'published' => 0,
            // ],
        ]);

    }
}
