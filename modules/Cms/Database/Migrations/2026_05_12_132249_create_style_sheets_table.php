<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $styleSheetsTable = modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');

        Schema::create($styleSheetsTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name');
            $table->string('slug')->unique('style_sheets_slug_unique');
            $table->string('driver', 32)->default('custom');
            $table->string('framework_version')->nullable();
            $table->string('framework_source', 16)->default('cdn');
            $table->json('definition')->nullable();
            $table->longText('scss_source')->nullable();
            $table->string('compiled_disk', 32)->nullable();
            $table->string('compiled_path', 512)->nullable();
            $table->string('compiled_checksum', 64)->nullable();
            $table->timestamp('compiled_at')->nullable();
            $table->string('compiler_version', 32)->nullable();

            // soft deletes and timestamps (no published column for style sheets)
            createDefaultExtraTableFields($table, true, false);
        });

    }

    public function down()
    {
        $styleSheetsTable = modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');
        Schema::dropIfExists($styleSheetsTable);
    }
};
