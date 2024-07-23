<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentServicesTable extends Migration
{
    public function up()
    {
        Schema::create('payment_services', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name');
			$table->string('title');

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

        
    }

    public function down()
    {
        Schema::dropIfExists('payment_services');
    }
}
