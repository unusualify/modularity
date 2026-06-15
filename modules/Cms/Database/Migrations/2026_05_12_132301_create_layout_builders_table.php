<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        $styleSheetsTable = modularousConfig('tables.cms_style_sheets', 'um_cms_style_sheets');

        Schema::create($layoutBuildersTable, function (Blueprint $table) use ($styleSheetsTable) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('name');
            $table->string('slug')->unique('layout_builders_slug_unique');
            $table->string('blade_source', 24)->default('db');
            $table->json('blade_segments')->nullable();
            $table->json('definition')->nullable();
            $table->json('style_sheet_slugs')->nullable();
            $table->string('blade_view_name', 512)->nullable();
            $table->foreignId('style_sheet_id')
                ->nullable()
                ->constrained($styleSheetsTable)
                ->nullOnDelete();

            // soft deletes and timestamps (no published column for layout builders)
            createDefaultExtraTableFields($table, true, false);
        });

    }

    public function down()
    {
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        Schema::dropIfExists($layoutBuildersTable);
    }
};
