<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table( unusualConfig('tables.users', 'users'), function (Blueprint $table) {
            $table->after('name', function ($table) {
                $table->string('surname',30)->nullable();
                $table->string('job_title')->nullable();
            });

            $table->after('email', function ($table) {
                $table->string('language')->default('en');
                $table->string('timezone')->default('Europe/London');
                $table->string('phone',20)->nullable();
                $table->string('country',30)->nullable();
            });

        });

    }

    public function down()
    {
        Schema::table( unusualConfig('tables.users', 'users'), function (Blueprint $table) {
            $table->dropColumn('surname');
            $table->dropColumn('job_title');
            $table->dropColumn('language');
            $table->dropColumn('timezone');
            $table->dropColumn('phone');
            $table->dropColumn('country');
        });
    }
}
