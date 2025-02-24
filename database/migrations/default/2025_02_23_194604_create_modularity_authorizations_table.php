<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $authorizationsTable = modularityConfig('tables.authorizations', 'modularity_authorizations');
        if (! Schema::hasTable($authorizationsTable)) {
            Schema::create($authorizationsTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');

                $table->uuidMorphs('authorized');
                $table->uuidMorphs('authorizable');
                // a "published" column, and soft delete and timestamps columns
                createDefaultExtraTableFields($table, false, false);
            });
        }


    }

    public function down()
    {
        $authorizationsTable = modularityConfig('tables.authorizations', 'modularity_authorizations');
        Schema::dropIfExists($authorizationsTable);
    }
};
