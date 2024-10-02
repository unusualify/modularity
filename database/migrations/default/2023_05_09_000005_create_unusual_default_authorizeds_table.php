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
        $authorizedsTable = unusualConfig('tables.authorizeds', 'modularity_authorizeds');
        $usersTable = unusualConfig('tables.users', 'admin_users');

        if (! Schema::hasTable($authorizedsTable)) {
            Schema::create($authorizedsTable, function (Blueprint $table) use ($usersTable) {
                $table->{unusualIncrementsMethod()}('id');
                $table->{unusualIntegerMethod()}('user_id')->unsigned();
                $table->foreign('user_id', "fk_{$usersTable}_authorized_id")
                    ->references('id')
                    ->on($usersTable)
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->string('authorizedable_type')->nullable(); // MODEL CLASS
                $table->string('authorizedable_id')->nullable(); // ID of model to be authorized
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $authorizedsTable = unusualConfig('tables.authorizeds', 'modularity_authorizeds');

        Schema::dropIfExists($authorizedsTable);
    }
};
