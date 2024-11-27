<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardTypesTable extends Migration
{
    public function up()
    {
        Schema::create('card_types', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name');
            $table->string('card_type');

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

    }

    public function down()
    {
        Schema::dropIfExists('card_types');
    }
}
