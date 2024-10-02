<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnusualDefaultMediasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $unusualMediasTable = unusualConfig('tables.medias', 'modularity_medias');
        $unusualMediablesTable = unusualConfig('tables.mediables', 'modularity_mediables');

        if (! Schema::hasTable($unusualMediasTable)) {
            Schema::create($unusualMediasTable, function (Blueprint $table) {
                $table->{unusualIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->text('uuid');
                $table->text('alt_text')->nullable();
                $table->integer('width')->unsigned();
                $table->integer('height')->unsigned();
                $table->text('caption')->nullable();
                $table->text('filename')->nullable();
            });
        }

        if (! Schema::hasTable($unusualMediablesTable)) {
            Schema::create($unusualMediablesTable, function (Blueprint $table) use ($unusualMediasTable) {
                $table->{unusualIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->{unusualIntegerMethod()}('mediable_id')->nullable()->unsigned();
                $table->string('mediable_type')->nullable();
                $table->{unusualIntegerMethod()}('media_id')->unsigned();
                $table->integer('crop_x')->nullable();
                $table->integer('crop_y')->nullable();
                $table->integer('crop_w')->nullable();
                $table->integer('crop_h')->nullable();
                $table->string('role')->nullable();
                $table->string('crop')->nullable();
                $table->string('locale', 6)->index();
                $table->text('lqip_data')->nullable();
                $table->string('ratio')->nullable();
                $table->json('metadatas');
                $table->foreign('media_id', 'fk_mediables_media_id')->references('id')->on($unusualMediasTable)->onDelete('cascade')->onUpdate('cascade');
                $table->index(['mediable_type', 'mediable_id']);

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
        $unusualMediasTable = unusualConfig('tables.medias', 'modularity_medias');
        $unusualMediablesTable = unusualConfig('tables.mediables', 'modularity_mediables');

        Schema::dropIfExists($unusualMediablesTable);
        Schema::dropIfExists($unusualMediasTable);
    }
}
