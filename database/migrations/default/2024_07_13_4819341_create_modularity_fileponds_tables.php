<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $filepondsTable = modularityConfig('tables.fileponds', 'modularity_fileponds');

        if (! Schema::hasTable($filepondsTable)) {
            Schema::create($filepondsTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->uuidMorphs('filepondable');
                $table->text('uuid');
                $table->text('file_name');
                $table->string('role');
                $table->string('locale');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $temporariesTable = modularityConfig('tables.filepond_temporaries', 'modularity_filepond_temporaries');

        if (! Schema::hasTable($temporariesTable)) {
            Schema::create($temporariesTable, function (Blueprint $table) {
                $table->{modularityIncrementsMethod()}('id');
                $table->string('file_name');
                $table->string('folder_name');
                $table->string('input_role');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        $filepondsTable = modularityConfig('tables.fileponds', 'modularity_fileponds');
        $temporariesTable = modularityConfig('tables.filepond_temporaries', 'modularity_filepond_temporaries');

        Schema::dropIfExists($filepondsTable);
        Schema::dropIfExists($temporariesTable);
    }
};
