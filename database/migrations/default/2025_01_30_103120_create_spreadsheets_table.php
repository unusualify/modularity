<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;


class CreateSpreadsheetsTable extends Migration
{
    public function up()
    {
        $modularitySpreadssheetsTable = modularityConfig('tables.spreadsheets', 'modularity_spreadsheets');

        Schema::create($modularitySpreadssheetsTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->uuidMorphs('spreadsheetable');
            $table->json('content')->default(new Expression('(JSON_ARRAY())'));
            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });


    }

    public function down()
    {
        Schema::dropIfExists(modularityConfig('tables.spreadsheets', 'modularity_spreadsheets'));

    }
}
