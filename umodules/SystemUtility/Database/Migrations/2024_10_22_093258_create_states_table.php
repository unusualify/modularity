<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('code');
            $table->string('color');
            $table->string('icon');
            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

        Schema::create('state_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'state');
            $table->string('name');
        });

    }

    public function down()
    {
        Schema::dropIfExists('state_translations');
        Schema::dropIfExists('states');
    }
}
