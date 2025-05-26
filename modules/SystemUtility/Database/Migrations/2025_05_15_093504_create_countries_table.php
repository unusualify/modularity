<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $countryTable = modularityConfig('tables.countries', 'um_countries');
        $countryTranslationTable = modularityConfig('tables.country_translations', 'um_country_translations');

        Schema::create($countryTable, function (Blueprint $table) {
            // this will create an id, name field
            createDefaultTableFields($table);
            $table->string('code', 2);
            $table->integer('phone_code');

            // a "published" column, and soft delete and timestamps columns
            createDefaultExtraTableFields($table);
        });

        Schema::create($countryTranslationTable, function (Blueprint $table) use ($countryTable) {
            createDefaultTranslationsTableFields($table, 'country', $countryTable);
            $table->string('name');
        });

    }

    public function down()
    {
        $countryTable = modularityConfig('tables.countries', 'um_countries');
        $countryTranslationTable = modularityConfig('tables.country_translations', 'um_country_translations');

        Schema::dropIfExists($countryTranslationTable);
        Schema::dropIfExists($countryTable);
    }
};
