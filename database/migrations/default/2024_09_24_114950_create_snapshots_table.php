<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{

    public function up()
    {
        $snapshotsTable= config('snapshot.table', 'snapshots');

        if(!Schema::hasTable($snapshotsTable)){
            Schema::create($snapshotsTable, function(Blueprint $table){
                $table->id();
                $table->uuidMorphs('snapshotable');
                $table->uuidMorphs('source');
                $table->json('data')->default(new Expression('(JSON_ARRAY())'));
                $table->timestamps();
            });
        }

    }


    public function down()
    {
        $snapshotsTable = config('snapshot.table', 'snapshots');

        Schema::dropIfExists($snapshotsTable);
    }
};
