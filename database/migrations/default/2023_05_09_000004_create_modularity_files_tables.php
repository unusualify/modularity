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
        $filesTable = modularityConfig('tables.files', 'modularity_files');
        $fileablesTable = modularityConfig('tables.fileables', 'modularity_fileables');

        if (! Schema::hasTable($filesTable)) {
            Schema::create($filesTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->text('uuid');
                $table->text('filename')->nullable();
                $table->integer('size')->unsigned();
            });
        }

        if (! Schema::hasTable($fileablesTable)) {
            Schema::create($fileablesTable, function (Blueprint $table) use ($filesTable) {
                $table->{modularityIncrementsMethod()}('id');
                $table->timestamps();
                $table->softDeletes();
                $table->{modularityIntegerMethod()}('file_id')->unsigned();
                $table->foreign('file_id', 'fk_files_file_id')->references('id')->on($filesTable)->onDelete('cascade')->onUpdate('cascade');
                // $table->{modularityIntegerMethod()}('fileable_id')->nullable()->unsigned();
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
        $filesTable = modularityConfig('tables.files', 'modularity_files');
        $fileablesTable = modularityConfig('tables.fileables', 'modularity_fileables');

        Schema::dropIfExists($fileablesTable);
        Schema::dropIfExists($filesTable);
    }
};
