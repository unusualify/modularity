<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnusualDefaultRepeatersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // dd(unusualBaseKey() . '.tables.repeaters', config(unusualBaseKey() . '.tables.repeaters'));
        $unusualRepeatersTable = 'umod_repeaters'; // config(unusualBaseKey() . '.tables.repeaters', 'modularity_files');
        // dd($unusualRepeatersTable);
        // $unusualRepeatersTable = config($base_key . '.tables.fileables', 'modularity_fileables');

        // if (!Schema::hasTable($unusualFilesTable)) {
        //     Schema::create($unusualFilesTable, function (Blueprint $table) {
        //         $table->{unusualIncrementsMethod()}('id');
        //         $table->timestamps();
        //         $table->softDeletes();
        //         $table->text('uuid');
        //         $table->text('filename')->nullable();
        //         $table->integer('size')->unsigned();
        //     });
        // }

        if (!Schema::hasTable($unusualRepeatersTable)) {
            Schema::create($unusualRepeatersTable, function (Blueprint $table) use ($unusualRepeatersTable) {
                $table->{unusualIncrementsMethod()}('id');
                $table->string('repeatable_type')->nullable(); // MODEL CLASS
                $table->string('repeatable_id')->nullable(); // ID belonging to repeatable_type
                $table->json('content');
                $table->string('role')->nullable(); // input name
                $table->string('locale', 6)->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['repeatable_type', 'repeatable_id']);
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
        // $unusualFilesTable = config(unusualBaseKey() . '.tables.files', 'modularity_files');
        $unusualRepeatersTable = config(unusualBaseKey() . '.tables.repeaters', 'modularity_fileables');

        Schema::dropIfExists($unusualRepeatersTable);
        // Schema::dropIfExists($unusualFilesTable);
    }
}
