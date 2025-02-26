<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
            $table->after('valid_till', function (Blueprint $table) {
                $table->string('role')->nullable();
                $table->string('locale')->nullable();
            });

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('priceable.tables.prices'), function (Blueprint $table) {
            $table->dropColumn('role');
            $table->dropColumn('locale');
        });
    }
};
