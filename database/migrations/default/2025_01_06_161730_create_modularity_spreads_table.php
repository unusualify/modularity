<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $modularitySpreadsTable = modularityConfig('tables.spreads', 'modularity_spreads');

        if (! Schema::hasTable($modularitySpreadsTable)) {
            Schema::create($modularitySpreadsTable, function (Blueprint $table) {
                createDefaultTableFields($table);
                $table->uuidMorphs('spreadable');
                $table->json('content')->default(new Expression('(JSON_ARRAY())'));
                $table->timestamps();
                $table->softDeletes();
            });

        }

    }

    public function down()
    {
        Schema::dropIfExists(modularityConfig('tables.spreads', 'modularity_spreads'));
    }
};
