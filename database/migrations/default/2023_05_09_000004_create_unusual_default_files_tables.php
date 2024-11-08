<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnusualDefaultFilesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $unusualFilesTable = unusualConfig('tables.files', 'modularity_files');
        $unusualFileablesTable = unusualConfig('tables.fileables', 'modularity_fileables');

        if (! Schema::hasTable($unusualFilesTable)) {
            Schema::create($unusualFilesTable, function (Blueprint $table) {
                $table->{unusualIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->text('uuid');
                $table->text('filename')->nullable();
                $table->integer('size')->unsigned();
            });
        }

        if (! Schema::hasTable($unusualFileablesTable)) {
            Schema::create($unusualFileablesTable, function (Blueprint $table) use ($unusualFilesTable) {
                $table->{unusualIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->{unusualIntegerMethod()}('file_id')->unsigned();
                $table->foreign('file_id', 'fk_files_file_id')->references('id')->on($unusualFilesTable)->onDelete('cascade')->onUpdate('cascade');
                // $table->{unusualIntegerMethod()}('fileable_id')->nullable()->unsigned();
                // $table->string('fileable_type')->nullable();
                $table->uuidMorphs('fileable');
                $table->string('role')->nullable();
                $table->string('locale', 6)->index();
                // $table->index(['fileable_type', 'fileable_id']);
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
        $unusualFilesTable = unusualConfig('tables.files', 'modularity_files');
        $unusualFileablesTable = unusualConfig('tables.fileables', 'modularity_fileables');

        Schema::dropIfExists($unusualFileablesTable);
        Schema::dropIfExists($unusualFilesTable);
    }
}
