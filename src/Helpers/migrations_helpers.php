<?php

use Illuminate\Support\Str;

if (! function_exists('modularityIncrementsMethod')) {
    /**
     * @return string
     */
    function modularityIncrementsMethod()
    {
        return modularityConfig('use_big_integers_on_migrations')
            ? 'bigIncrements'
            : 'increments';
    }
}

if (! function_exists('modularityIntegerMethod')) {
    /**
     * @return string
     */
    function modularityIntegerMethod()
    {
        return modularityConfig('use_big_integers_on_migrations')
            ? 'bigInteger'
            : 'integer';
    }
}

if (! function_exists('createDefaultFields')) {
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
        $table->{modularityIncrementsMethod()}('id');
        // $table->string('name');
    }
}

if (! function_exists('createDefaultExtraTableFields')) {
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

if (! function_exists('createDefaultTranslationsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultTranslationsTableFields($table, $modelName, $tableName = null)
    {
        $modelSnakeName = Str::snake($modelName);

        if (! $tableName) {
            $tableName = Str::plural(Str::snake($modelName));
        }

        $table->{modularityIncrementsMethod()}('id');
        $table->{modularityIntegerMethod()}("{$modelSnakeName}_id")->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('locale', 7)->index();
        $table->boolean('active')->default(true);

        $foreignIndexName = "fk_{$modelSnakeName}_translations_{$modelSnakeName}_id";

        if (mb_strlen($modelName) > 18) {
            $shortcut = abbreviation($modelSnakeName);
            $foreignIndexName = "fk_{$modelSnakeName}_translations_{$shortcut}_id";
        }

        $table->foreign("{$modelSnakeName}_id", $foreignIndexName)
            ->references('id')
            ->on($tableName)
            ->onDelete('CASCADE');

        $table->unique(["{$modelSnakeName}_id", 'locale'], "{$modelSnakeName}_id_locale_unique");
    }
}

if (! function_exists('createDefaultSlugsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultSlugsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (! $tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{modularityIncrementsMethod()}('id');
        $table->{modularityIntegerMethod()}("{$tableNameSingular}_id")->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('slug');
        $table->string('locale', 7)->index();
        $table->boolean('active');
        $table->foreign("{$tableNameSingular}_id", "fk_{$tableNameSingular}_slugs_{$tableNameSingular}_id")->references('id')->on($tableNamePlural)->onDelete('CASCADE')->onUpdate('NO ACTION');
    }
}

if (! function_exists('createDefaultRelationshipTableFields')) {
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
        if (! $table1NamePlural) {
            $table1NamePlural = Str::plural($table1NameSingular);
        }
        if (! $table2NamePlural) {
            $table2NamePlural = Str::plural($table2NameSingular);
        }

        $table1ForeignKey = "{$table1NameSingular}_id";
        $table2ForeignKey = "{$table2NameSingular}_id";

        // $table->{modularityIntegerMethod()}("{$table1NameSingular}_id")->unsigned();
        // $table->{modularityIntegerMethod()}("{$table2NameSingular}_id")->unsigned();
        // $table1IndexName = $table1NameSingular;
        // $table2IndexName = $table2NameSingular;
        // if( strlen($table1IndexName) > 12){
        //     $shortcut = abbreviation($table1IndexName);
        //     $table1IndexName = "{$shortcut}";
        // }
        // if( strlen($table2IndexName) > 12){
        //     $shortcut = abbreviation($table2IndexName);
        //     $table2IndexName= "{$shortcut}";
        // }
        // $table->foreign("{$table1NameSingular}_id", "fk_{$table1NameSingular}_{$table2NameSingular}_{$table1IndexName}_id")->references('id')->on($table1NamePlural)->onDelete('cascade');
        // $table->foreign("{$table2NameSingular}_id", "fk_{$table1NameSingular}_{$table2NameSingular}_{$table2IndexName}_id")->references('id')->on($table2NamePlural)->onDelete('cascade');
        // $table->index(["{$table2NameSingular}_id", "{$table1NameSingular}_id"], "idx_{$table1IndexName}_{$table2IndexName}_" . Str::random(5));

        $table->foreignId($table1ForeignKey)
            ->constrained($table1NamePlural)
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->foreignId($table2ForeignKey)
            ->constrained($table2NamePlural)
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table->primary([$table1ForeignKey, $table2ForeignKey]);
    }
}

if (! function_exists('createDefaultMorphPivotTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string|null $modelName table
     * @param string|null $tableName tableables
     * @return void
     */
    function createDefaultMorphPivotTableFields($table, $modelName = null, $tableName = null, $morphedTableName = null)
    {
        if (! $modelName && ! $tableName) {
            throw new \Exception('modelName or tableName is required');
        }

        if (! $modelName) {
            $modelName = getMorphModelName($tableName);
        } else {
            $modelName = getMorphModelName($modelName); // guarentee model name with clearing able|ables
        }

        if (! $tableName) {
            $tableName = makeMorphPivotTableName($modelName);
        }

        if (! $morphedTableName) {
            $morphedTableName = tableName($modelName);
        }

        $foreignKey = makeForeignKey($modelName); // *_id
        $morphName = makeMorphName($modelName);

        $table->foreignId($foreignKey)
            ->constrained($morphedTableName)
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->uuidMorphs($morphName, "{$tableName}_type_id_index");
    }
}

if (! function_exists('createDefaultRevisionsTableFields')) {
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultRevisionsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (! $tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{modularityIncrementsMethod()}('id');
        $table->{modularityIntegerMethod()}("{$tableNameSingular}_id")->unsigned();
        $table->{modularityIntegerMethod()}('user_id')->unsigned()->nullable();

        $table->timestamps();
        $table->json('payload');
        $table->foreign("{$tableNameSingular}_id")->references('id')->on("{$tableNamePlural}")->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on(modularityConfig('tables.users', 'admin_users'))->onDelete('set null');
    }
}
