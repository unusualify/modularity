<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Support\PublishableMetadata;
use Unusualify\Modularous\Support\TranslatableMetadata;

if (! function_exists('modularousIncrementsMethod')) {
    /**
     * @return string
     */
    function modularousIncrementsMethod()
    {
        return modularousConfig('use_big_integers_on_migrations')
            ? 'bigIncrements'
            : 'increments';
    }
}

if (! function_exists('modularousIntegerMethod')) {
    /**
     * @return string
     */
    function modularousIntegerMethod()
    {
        return modularousConfig('use_big_integers_on_migrations')
            ? 'bigInteger'
            : 'integer';
    }
}

if (! function_exists('createDefaultFields')) {
    /**
     * @param Blueprint $table
     * @param bool $softDeletes
     * @param bool $published
     * @param bool $publishDates
     * @param bool $visibility
     * @return void
     */
    function createDefaultTableFields($table, $has_name = true)
    {
        $table->{modularousIncrementsMethod()}('id');
        // $table->string('name');
    }
}

if (! function_exists('createDefaultExtraTableFields')) {
    /**
     * @param Blueprint $table
     * @param bool $softDeletes
     * @param bool $published
     * @param bool $publishDates
     * @param bool $visibility
     * @return void
     */
    function createDefaultExtraTableFields($table, $softDeletes = true, $published = true, $publishDates = false, $visibility = false)
    {
        PublishableMetadata::addColumns($table, $published, $publishDates);

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
     * @param Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultTranslationsTableFields($table, $modelName, $tableName = null, $foreignKey = null)
    {
        $modelSnakeName = Str::snake($modelName);

        if (! $tableName) {
            $tableName = Str::plural(Str::snake($modelName));
        }

        $foreignKey ??= "{$modelSnakeName}_id";

        $table->{modularousIncrementsMethod()}('id');
        $table->{modularousIntegerMethod()}($foreignKey)->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('locale', 7)->index();
        $table->boolean('active')->default(true);

        $foreignIndexName = "fk_{$modelSnakeName}_translations_{$foreignKey}";

        if (mb_strlen($modelName) > 18) {
            $shortcut = abbreviation($modelSnakeName);
            $foreignIndexName = "fk_{$modelSnakeName}_translations_{$shortcut}_id";
        }

        $table->foreign($foreignKey, $foreignIndexName)
            ->references('id')
            ->on($tableName)
            ->onDelete('CASCADE');

        $table->unique([$foreignKey, 'locale'], "{$modelSnakeName}_id_locale_unique");
    }
}

if (! function_exists('createDefaultSlugsTableFields')) {
    /**
     * @param Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultSlugsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (! $tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{modularousIncrementsMethod()}('id');
        $table->{modularousIntegerMethod()}("{$tableNameSingular}_id")->unsigned();

        $table->softDeletes();
        $table->timestamps();
        $table->string('slug');
        $table->string('locale', 7)->index();
        $table->boolean('active')->default(true);
        $table->foreign("{$tableNameSingular}_id", "fk_{$tableNameSingular}_slugs_{$tableNameSingular}_id")->references('id')->on($tableNamePlural)->onDelete('CASCADE')->onUpdate('NO ACTION');
    }
}

if (! function_exists('createDefaultRelationshipTableFields')) {
    /**
     * @param Blueprint $table
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

        // $table->{modularousIntegerMethod()}("{$table1NameSingular}_id")->unsigned();
        // $table->{modularousIntegerMethod()}("{$table2NameSingular}_id")->unsigned();
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
     * @param Blueprint $table
     * @param string|null $modelName table
     * @param string|null $tableName tableables
     * @return void
     */
    function createDefaultMorphPivotTableFields($table, $modelName = null, $tableName = null, $morphedTableName = null)
    {
        if (! $modelName && ! $tableName) {
            throw new Exception('modelName or tableName is required');
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
     * Standard revision table: payload, optional lineage (source_id), and workflow columns (status, approved_at, approved_by).
     * Pending state is represented by the latest row’s status only — no column on the subject model.
     *
     * @param Blueprint $table
     * @param string $tableNameSingular
     * @param string|null $tableNamePlural
     * @return void
     */
    function createDefaultRevisionsTableFields($table, $tableNameSingular, $tableNamePlural = null)
    {
        if (! $tableNamePlural) {
            $tableNamePlural = Str::plural($tableNameSingular);
        }

        $table->{modularousIncrementsMethod()}('id');
        $table->{modularousIntegerMethod()}("{$tableNameSingular}_id")->unsigned();
        $table->{modularousIntegerMethod()}('user_id')->unsigned()->nullable();
        $table->unsignedBigInteger('source_id')->nullable();

        $table->string('status', 32)->default('approved');
        $table->timestamp('approved_at')->nullable();
        $table->{modularousIntegerMethod()}('approved_by')->unsigned()->nullable();

        $table->timestamps();
        $table->json('payload');
        $table->foreign("{$tableNameSingular}_id")->references('id')->on("{$tableNamePlural}")->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on(modularousConfig('tables.users', 'um_users'))->onDelete('set null');
        $table->foreign('approved_by')->references('id')->on(modularousConfig('tables.users', 'um_users'))->onDelete('set null');
    }
}

if (! function_exists('createTranslatableMetadataFields')) {
    /**
     * Translatable metadata (SEO, canonical, robots, sitemap flag) columns for {@code *_translations} tables.
     *
     * Mirrors {@see TranslatableMetadata::TRANSLATED_ATTRIBUTES}; use with {@see HasTranslatableMetadata}.
     *
     * @param bool $withSitemapInclude When false, omits sitemap_include (not recommended for new modules).
     */
    function createTranslatableMetadataFields(Blueprint $table, bool $withSitemapInclude = true): void
    {
        TranslatableMetadata::addColumns($table, $withSitemapInclude);
    }
}
