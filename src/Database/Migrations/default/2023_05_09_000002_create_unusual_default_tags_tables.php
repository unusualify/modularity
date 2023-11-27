<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnusualDefaultTagsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $unusualTaggedTable = config(unusualBaseKey() . '.tables.tagged', 'tagged');

        if (!Schema::hasTable($unusualTaggedTable)) {
            Schema::create($unusualTaggedTable, function (Blueprint $table) {
                $table->{unusualIncrementsMethod()}('id');
                $table->string('taggable_type');
                $table->integer('taggable_id')->unsigned();
                $table->integer('tag_id')->unsigned();
                $table->index(['taggable_type', 'taggable_id']);
            });
        }


        $unusualTagsTable = config(unusualBaseKey() . '.tables.tags', 'tags');

        if (!Schema::hasTable($unusualTagsTable)) {
            Schema::create($unusualTagsTable, function (Blueprint $table) {
                $table->{unusualIncrementsMethod()}('id');
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
        Schema::dropIfExists(config(unusualBaseKey() . '.tables.tags', 'tags'));
        Schema::dropIfExists(config(unusualBaseKey() . '.tables.tagged', 'tagged'));
    }
}
