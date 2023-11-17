<?php

use Illuminate\Support\Str;

if (!function_exists('unusualIncrementsMethod')) {
    /**
     * @return string
     */
    function unusualIncrementsMethod()
    {
        return config(unusualBaseKey().'.use_big_integers_on_migrations')
            ? 'bigIncrements'
            : 'increments';
    }
}

if (!function_exists('unusualIntegerMethod')) {
    /**
     * @return string
     */
    function unusualIntegerMethod()
    {
        return config(unusualBaseKey().'.use_big_integers_on_migrations')
            ? 'bigInteger'
            : 'integer';
    }
}

if (!function_exists('createDefaultFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool $softDeletes
     * @param bool $published
     * @param bool $publishDates
     * @param bool $visibility
     * @return void
     */
    function createDefaultTableFields($table, $has_name = true)
    {
        $table->{unusualIncrementsMethod()}('id');
        // $table->string('name');
    }
}

if (!function_exists('createDefaultExtraTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param bool $softDeletes
     * @param bool $published
     * @param bool $publishDates
     * @param bool $visibility
     * @return void
     */
    function createDefaultExtraTableFields($table, $softDeletes = true, $published = true, $publishDates = false, $visibility = false)
    {

        if ($published) {
            $table->boolean('published')->default(false);
        }

        if ($publishDates) {
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamp('publish_end_date')->nullable();
        }

        if ($visibility) {
            $table->boolean('public')->default(true);
        }

        $table->timestamps();

        if ($softDeletes) {
            $table->softDeletes();
        }
    }
}

if (!function_exists('createDefaultTranslationsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultTranslationsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (!$tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{unusualIncrementsMethod()}('id');
        $table->{unusualIntegerMethod()}("{$tableNameSingular}_id")->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('locale', 7)->index();
        $table->boolean('active');

        $foreignIndexName = "fk_{$tableNameSingular}_translations_{$tableNameSingular}_id";

        if( strlen($tableNameSingular) > 18){
            $shortcut = abbreviation($tableNameSingular);
            $foreignIndexName = "fk_{$tableNameSingular}_translations_{$shortcut}_id";
        }

        $table->foreign("{$tableNameSingular}_id", $foreignIndexName)
            ->references('id')
            ->on($tableNamePlural)
            ->onDelete('CASCADE');
        $table->unique(["{$tableNameSingular}_id", 'locale'], "{$tableNameSingular}_id_locale_unique");
    }
}

if (!function_exists('createDefaultSlugsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultSlugsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (!$tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{unusualIncrementsMethod()}('id');
        $table->{unusualIntegerMethod()}("{$tableNameSingular}_id")->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('slug');
        $table->string('locale', 7)->index();
        $table->boolean('active');
        $table->foreign("{$tableNameSingular}_id", "fk_{$tableNameSingular}_slugs_{$tableNameSingular}_id")->references('id')->on($tableNamePlural)->onDelete('CASCADE')->onUpdate('NO ACTION');
    }
}

if (!function_exists('createDefaultRelationshipTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $table1NameSingular
     * @param string $table2NameSingular
     * @param string|null $table1NamePlural
     * @param string|null $table2NamePlural
     * @return void
     */
    function createDefaultRelationshipTableFields($table, $table1NameSingular, $table2NameSingular, $table1NamePlural = null, $table2NamePlural = null)
    {
        if (!$table1NamePlural) {
            $table1NamePlural = Str::plural($table1NameSingular);
        }
        if (!$table2NamePlural) {
            $table2NamePlural = Str::plural($table2NameSingular);
        }

        $table->{unusualIntegerMethod()}("{$table1NameSingular}_id")->unsigned();
        $table->{unusualIntegerMethod()}("{$table2NameSingular}_id")->unsigned();

        $table1IndexName = $table1NameSingular;
        $table2IndexName = $table2NameSingular;

        if( strlen($table1IndexName) > 12){
            $shortcut = abbreviation($table1IndexName);
            $table1IndexName = "{$shortcut}";
        }

        if( strlen($table2IndexName) > 12){
            $shortcut = abbreviation($table2IndexName);
            $table2IndexName= "{$shortcut}";
        }

        $table->foreign("{$table1NameSingular}_id", "fk_{$table1NameSingular}_{$table2NameSingular}_{$table1IndexName}_id")->references('id')->on($table1NamePlural)->onDelete('cascade');
        $table->foreign("{$table2NameSingular}_id", "fk_{$table1NameSingular}_{$table2NameSingular}_{$table2IndexName}_id")->references('id')->on($table2NamePlural)->onDelete('cascade');

        $table->index(["{$table2NameSingular}_id", "{$table1NameSingular}_id"], "idx_{$table1IndexName}_{$table2IndexName}_" . Str::random(5));
    }
}

if (!function_exists('createDefaultRevisionsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultRevisionsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (!$tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{unusualIncrementsMethod()}('id');
        $table->{unusualIntegerMethod()}("{$tableNameSingular}_id")->unsigned();
        $table->{unusualIntegerMethod()}('user_id')->unsigned()->nullable();

        $table->timestamps();
        $table->json('payload');
        $table->foreign("{$tableNameSingular}_id")->references('id')->on("{$tableNamePlural}")->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on(config(unusualBaseKey().'.users_table_name', 'users'))->onDelete('set null');
    }
}
