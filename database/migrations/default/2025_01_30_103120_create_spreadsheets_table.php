<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpreadsheetsTable extends Migration
{
    public function up()
    {
        Schema::create('spreadsheets', function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            
            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

        
    }

    public function down()
    {
        Schema::dropIfExists('spreadsheets');
    }
}
