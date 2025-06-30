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
        $creatorRecordsTable = modularityConfig('tables.creator_records', 'modularity_creator_records');

        if (! Schema::hasTable($creatorRecordsTable)) {
            Schema::create($creatorRecordsTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                // $table->{modularityIntegerMethod()}('user_id')->unsigned();

                $table->uuidMorphs('creator');
                $table->string('guard_name'); // For MySQL 8.0 use string('guard_name', 125);

                $table->uuidMorphs('creatable');

                // $table->foreign('user_id', "fk_{$usersTable}_authorized_id")
                //     ->references('id')
                //     ->on($usersTable)
                //     ->onDelete('cascade')
                //     ->onUpdate('cascade');
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
        $creatorRecordsTable = modularityConfig('tables.creator_records', 'modularity_creator_records');

        Schema::dropIfExists($creatorRecordsTable);
    }
};
