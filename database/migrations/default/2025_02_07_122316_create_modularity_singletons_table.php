<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Facades\Modularity;

return new class extends Migration
{
    public function up()
    {
        $table = Modularity::config('tables.singletons', 'modularity_singletons');

        Schema::create($table, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('singleton_type');
            $table->json('content')->default(new Expression('(JSON_ARRAY())'));

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table, published: false);
        });

    }

    public function down()
    {
        $table = Modularity::config('tables.singletons', 'modularity_singletons');
        Schema::dropIfExists($table);
    }
};
