<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration{

    public function up()
    {
        $tableName = unusualConfig('tables.assets', 'modularity_assets');

        if(!Schema::hasTable($tableName)){
            Schema::create($tableName, function(Blueprint $table){
                $table->{unusualIncrementsMethod()}('id');
                $table->unsignedBigInteger('assetable_id');
                $table->string('assetable_type');
                $table->index(['assetable_id', 'assetable_type']);
                $table->text('uuid');
                $table->text('file_name');
                $table->string('role');
                $table->string('locale');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }


    public function down()
    {
        $tableName = unusualConfig('tables.assets', 'modularity_assets');
        Schema::dropIfExists($tableName);
    }
};
