<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(modularityConfig('tables.companies', 'um_companies'), function (Blueprint $table) {
            $table->string('name', 99)->nullable()->change();
            $table->string('city', 50)->nullable()->change();
            $table->string('state', 50)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table(modularityConfig('tables.companies', 'um_companies'), function (Blueprint $table) {
            $table->string('name', 30)->nullable()->change();
            $table->string('city', 30)->nullable()->change();
            $table->string('state', 30)->nullable()->change();
        });
    }
};
