<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modularityTaggedTable = modularityConfig('tables.tagged', 'modularity_tagged');

        if (! Schema::hasTable($modularityTaggedTable)) {
            Schema::create($modularityTaggedTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->uuidMorphs('taggable');
                // $table->string('taggable_type');
                // $table->integer('taggable_id')->unsigned();
                $table->integer('tag_id')->unsigned();
                // $table->index(['taggable_type', 'taggable_id']);
            });
        }

        $modularityTagsTable = modularityConfig('tables.tags', 'modularity_tags');

        if (! Schema::hasTable($modularityTagsTable)) {
            Schema::create($modularityTagsTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->string('namespace');
                $table->string('slug');
                $table->string('name');
                $table->integer('count')->default(0)->unsigned();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(modularityConfig('tables.tags', 'modularity_tags'));
        Schema::dropIfExists(modularityConfig('tables.tagged', 'modularity_tagged'));
    }
};
