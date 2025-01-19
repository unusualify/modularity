<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralsTable extends Migration
{
    public function up()
    {
        Schema::create('generals', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name');

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

    }

    public function down()
    {
        Schema::dropIfExists('generals');
    }
}
