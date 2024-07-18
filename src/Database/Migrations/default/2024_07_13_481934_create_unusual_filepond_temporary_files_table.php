<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        $tableName = unusualConfig('tables.temporary_assets', 'modularity_temporary_assets');

        if(!Schema::hasTable($tableName))
        {
            Schema::create($tableName, function(Blueprint $table){
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
        $tableName = unusualConfig('tables.temporary_assets', 'modularity_temporary_assets');

        Schema::dropIfExists($tableName);

    }
};
