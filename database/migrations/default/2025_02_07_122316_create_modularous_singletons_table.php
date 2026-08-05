<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\Modularous;

return new class extends Migration
{
    public function up()
    {
        $singletonsTable = Modularous::config('tables.singletons', 'modularous_singletons');
        $revisionsTable = Modularous::config('tables.singleton_revisions', 'modularous_singleton_revisions');

        Schema::create($singletonsTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('singleton_type');
            $table->json('content')->default(new Expression('(JSON_ARRAY())'));

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table, published: false);
        });

        Schema::create($revisionsTable, function (Blueprint $table) use ($singletonsTable) {
            createDefaultRevisionsTableFields($table, 'singleton', $singletonsTable);
        });
    }

    public function down()
    {
        $singletonsTable = Modularous::config('tables.singletons', 'modularous_singletons');
        $revisionsTable = Modularous::config('tables.singleton_revisions', 'modularous_singleton_revisions');

        Schema::dropIfExists($revisionsTable);
        Schema::dropIfExists($singletonsTable);
    }
};
