<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;


class CreateSpreadsheetsTable extends Migration
{
    public function up()
    {
        $modularitySpreadsheetsTable = modularityConfig('tables.spreadsheets', 'modularity_spreadsheets');

        Schema::create($modularitySpreadsheetsTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->uuidMorphs('spreadsheetable');
            $table->json('content')->default(new Expression('(JSON_ARRAY())'));
            $table->string('role')->nullable();
            $table->string('locale')->nullable();

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });


    }

    public function down()
    {
        Schema::dropIfExists(modularityConfig('tables.spreadsheets', 'modularity_spreadsheets'));

    }
}
