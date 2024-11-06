<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up()
    {
        Schema::table('states', function (Blueprint $table) {
            // this will create an id, name field
			$table->string('color');
			$table->string('icon');
            // a "published" column, and soft delete and timestamps columns
        });


    }

    public function down()
    {
        // Schema::dropIfExists('state_translations');
		// Schema::dropIfExists('states');
    }
}
