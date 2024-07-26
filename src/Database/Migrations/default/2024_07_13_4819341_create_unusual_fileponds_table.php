<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{

    public function up()
    {
        $filepondsTable = unusualConfig('tables.fileponds', 'modularity_fileponds');

        if(!Schema::hasTable($filepondsTable)){
            Schema::create($filepondsTable, function(Blueprint $table){
                $table->{unusualIncrementsMethod()}('id');
                $table->unsignedBigInteger('filepondable_id');
                $table->string('filepondable_type');
                $table->index(['filepondable_id', 'filepondable_type']);
                $table->text('uuid');
                $table->text('file_name');
                $table->string('role');
                $table->string('locale');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $temporariesTable = unusualConfig('tables.temporary_fileponds', 'modularity_temporary_fileponds');

        if(!Schema::hasTable($temporariesTable))
        {
            Schema::create($temporariesTable, function(Blueprint $table){
                $table->{unusualIncrementsMethod()}('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }


    public function down()
    {
        $filepondsTable = unusualConfig('tables.fileponds', 'modularity_fileponds');
        $temporariesTable = unusualConfig('tables.temporary_fileponds', 'modularity_temporary_fileponds');

        Schema::dropIfExists($filepondsTable);
        Schema::dropIfExists($temporariesTable);
    }
};
