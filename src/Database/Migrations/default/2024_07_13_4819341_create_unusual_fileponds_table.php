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
                $table->uuidMorphs('filepondable');
                $table->text('uuid');
                $table->text('file_name');
                $table->string('role');
                $table->string('locale');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $temporariesTable = unusualConfig('tables.filepond_temporaries', 'modularity_filepond_temporaries');

        if(!Schema::hasTable($temporariesTable))
        {
            Schema::create($temporariesTable, function(Blueprint $table){
                $table->{unusualIncrementsMethod()}('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
            });
        }
    }


    public function down()
    {
        $filepondsTable = unusualConfig('tables.fileponds', 'modularity_fileponds');
        $temporariesTable = unusualConfig('tables.filepond_temporaries', 'modularity_filepond_temporaries');

        Schema::dropIfExists($filepondsTable);
        Schema::dropIfExists($temporariesTable);
    }
};
