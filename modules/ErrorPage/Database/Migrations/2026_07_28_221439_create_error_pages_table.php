<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(modularousConfig('tables.cms_error_pages', 'um_cms_error_pages'), function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name');
            $table->string('error_code', 16);
            $table->unique('error_code');

            createDefaultExtraTableFields($table, true, true, false, false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(modularousConfig('tables.cms_error_pages', 'um_cms_error_pages'));
    }
};
