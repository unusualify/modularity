<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpreadsTable extends Migration
{
    public function up()
    {
        $modularitySpreadsTable = unusualConfig('tables.spreads', 'modularity_spreads');

        if(! Schema::hasTable($modularitySpreadsTable)){
            Schema::create($modularitySpreadsTable, function (Blueprint $table) {
                createDefaultTableFields($table);
                $table->string('spreadable_type');
                $table->{unusualIntegerMethod()}('spreadable_id')->nullable()->unsigned();
                $table->json('json')->default(new Expression('(JSON_ARRAY())'));
                // $table->uuidMorphs('chatable');
                $table->timestamps();
                $table->softDeletes();
            });

        }

    }

    public function down()
    {
        Schema::dropIfExists(unusualConfig('tables.spreads', 'modularity_spreads'));
    }
}
