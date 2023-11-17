<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unusual_companies', function (Blueprint $table) {
            // this will create an id, a "published" column, and soft delete and timestamps columns
            createDefaultTableFields($table);
            // $table->{unusualIntegerMethod()}("_id")->unsigned();
            $table->string('name',30)->nullable();
            $table->text('address')->nullable();
            $table->string('city',30)->nullable();
            $table->string('state',30)->nullable();
            $table->string('country',30)->nullable();
            $table->string('zip_code',10)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('vat_number',20)->nullable();
            $table->string('tax_id',30)->nullable();
        });


    }

    public function down()
    {
        Schema::dropIfExists('unusual_companies');
    }
};
