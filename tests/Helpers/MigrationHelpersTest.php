<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Tests\TestCase;

class MigrationHelpersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function check_default_fields_of_extra_table_creation()
    {
        Schema::create('product_extras', function (Blueprint $table) {
            createDefaultExtraTableFields($table);
        });

        $this->assertTrue(Schema::hasColumns('product_extras', [
            'published',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));

        $columns = Schema::getColumns('product_extras');
        $publishedColumn = collect($columns)->firstWhere('name', 'published');
        // $this->assertEquals(false, $publishedColumn['default']);
        $this->assertEquals("'0'", $publishedColumn['default']);
    }

    /**
     * @test
     */
    public function check_extra_table_creation_with_publish_dates()
    {
        Schema::create('product_extras', function (Blueprint $table) {
            createDefaultExtraTableFields($table, true, true, true);
        });

        $this->assertTrue(Schema::hasColumns('product_extras', [
            'published',
            'publish_start_date',
            'publish_end_date',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));

        $columns = Schema::getColumns('product_extras');

        $startDateColumn = collect($columns)->firstWhere('name', 'publish_start_date');
        $endDateColumn = collect($columns)->firstWhere('name', 'publish_end_date');

        $this->assertEquals('datetime', $startDateColumn['type']);
        $this->assertEquals('datetime', $endDateColumn['type']);
        $this->assertTrue($startDateColumn['nullable']);
        $this->assertTrue($endDateColumn['nullable']);
    }

    /**
     * @test
     */
    public function check_extra_table_creation_with_visibility()
    {
        Schema::create('product_extras', function (Blueprint $table) {
            createDefaultExtraTableFields($table, true, true, false, true);
        });

        $this->assertTrue(Schema::hasColumns('product_extras', [
            'published',
            'public',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));

        $columns = Schema::getColumns('product_extras');
        $publicColumn = collect($columns)->firstWhere('name', 'public');

        $this->assertEquals('tinyint(1)', $publicColumn['type']);
        $this->assertEquals(true, $publicColumn['default']);
    }

    /**
     * @test
     */
    public function check_extra_table_creation_without_soft_deletes()
    {
        Schema::create('product_extras', function (Blueprint $table) {
            createDefaultExtraTableFields($table, false);
        });

        $this->assertFalse(Schema::hasColumn('product_extras', 'deleted_at'));
        $this->assertTrue(Schema::hasColumns('product_extras', [
            'published',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * @test
     */
    public function check_types_of_fields_of_extra_table_creation()
    {
        Schema::create('product_extras', function (Blueprint $table) {
            createDefaultExtraTableFields($table, true, true, true, true);
        });

        $columns = Schema::getColumns('product_extras');

        $this->assertEquals('tinyint(1)', collect($columns)->firstWhere('name', 'published')['type']);
        $this->assertEquals('datetime', collect($columns)->firstWhere('name', 'publish_start_date')['type']);
        $this->assertEquals('datetime', collect($columns)->firstWhere('name', 'publish_end_date')['type']);
        $this->assertEquals('tinyint(1)', collect($columns)->firstWhere('name', 'public')['type']);
        $this->assertEquals('datetime', collect($columns)->firstWhere('name', 'created_at')['type']);
        $this->assertEquals('datetime', collect($columns)->firstWhere('name', 'updated_at')['type']);
        $this->assertEquals('datetime', collect($columns)->firstWhere('name', 'deleted_at')['type']);
    }

    /**
     * @test
     */
    public function check_fields_of_translations_table_creation_with_only_model_name()
    {
        Schema::create('product_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, modelName: 'Product');
        });

        $this->assertTrue(Schema::hasColumns('product_translations', [
            'id',
            'product_id',
            'deleted_at',
            'created_at',
            'updated_at',
            'locale',
            'active',
        ]));
    }

    /**
     * @test
     */
    public function check_fields_of_translations_table_creation_with_model_name_and_table_name()
    {
        Schema::create('product_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, modelName: 'Product', tableName: 'products');
        });

        $this->assertTrue(Schema::hasColumns('product_translations', [
            'id',
            'product_id',
            'deleted_at',
            'created_at',
            'updated_at',
            'locale',
            'active',
        ]));
    }

    /**
     * @test
     */
    public function check_types_of_fields_of_translations_table_creation()
    {
        Schema::create('product_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'Product');
        });

        $columns = Schema::getColumns('product_translations');

        $this->assertEquals('integer', $columns[0]['type']); // id
        $this->assertEquals('integer', $columns[1]['type']); // product_id
        $this->assertEquals('datetime', $columns[2]['type']); // deleted_at
        $this->assertEquals('datetime', $columns[3]['type']); // created_at
        $this->assertEquals('datetime', $columns[4]['type']); // updated_at
        $this->assertEquals('varchar', $columns[5]['type']); // locale
        $this->assertEquals('tinyint(1)', $columns[6]['type']); // active
    }

    /**
     * @test
     */
    public function check_foreign_key_and_unique_constraint_for_translations_table()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('product_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'Product');
        });

        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('product_translations');

        $this->assertCount(1, $foreignKeys);
        $this->assertEquals('product_id', $foreignKeys[0]->getLocalColumns()[0]);
        $this->assertEquals('products', $foreignKeys[0]->getForeignTableName());
        $this->assertEquals('id', $foreignKeys[0]->getForeignColumns()[0]);

        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes('product_translations');

        $this->assertTrue(isset($indexes['product_id_locale_unique']));
        $this->assertTrue($indexes['product_id_locale_unique']->isUnique());
    }

    /**
     * @test
     */
    public function check_fields_of_morph_pivot_table_creation_with_only_model_name()
    {
        Schema::create('package_stateables', function (Blueprint $table) {
            createDefaultMorphPivotTableFields($table, modelName: 'PackageState');
        });

        $this->assertTrue(Schema::hasColumns('package_stateables', ['package_state_id', 'package_stateable_id', 'package_stateable_type']));
    }

    /**
     * @test
     */
    public function check_fields_of_morph_pivot_table_creation_with_model_name_and_table_name()
    {
        Schema::create('package_stateables', function (Blueprint $table) {
            createDefaultMorphPivotTableFields($table, modelName: 'PackageState', tableName: 'package_stateables');
        });

        $this->assertTrue(Schema::hasColumns('package_stateables', ['package_state_id', 'package_stateable_id', 'package_stateable_type']));
    }

    /**
     * @test
     */
    public function check_fields_of_morph_pivot_table_creation_with_only_table_name()
    {
        Schema::create('package_stateables', function (Blueprint $table) {
            createDefaultMorphPivotTableFields($table, tableName: 'package_stateables');
        });

        $this->assertTrue(Schema::hasColumns('package_stateables', ['package_state_id', 'package_stateable_id', 'package_stateable_type']));
    }

    /**
     * @test
     */
    public function check_types_of_fields_of_morph_pivot_table_creation()
    {
        Schema::create('package_stateables', function (Blueprint $table) {
            createDefaultMorphPivotTableFields($table, 'PackageState');
        });

        $columns = Schema::getColumns('package_stateables');

        $this->assertEquals('integer', $columns[0]['type']);
        $this->assertEquals('varchar', $columns[1]['type']);
        $this->assertEquals('varchar', $columns[2]['type']);
    }

    /**
     * @test
     */
    public function check_fields_of_relationship_table_creation_with_singular_names()
    {
        Schema::create('product_category', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'product', 'category');
        });

        $this->assertTrue(Schema::hasColumns('product_category', [
            'product_id',
            'category_id',
        ]));
    }

    /**
     * @test
     */
    public function check_fields_of_relationship_table_creation_with_plural_names()
    {
        Schema::create('product_category', function (Blueprint $table) {
            createDefaultRelationshipTableFields(
                $table,
                'product',
                'category',
                'products',
                'categories'
            );
        });

        $this->assertTrue(Schema::hasColumns('product_category', [
            'product_id',
            'category_id',
        ]));
    }

    /**
     * @test
     */
    public function check_primary_key_constraint_for_relationship_table()
    {
        Schema::create('product_category', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'product', 'category');
        });

        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes('product_category');

        $this->assertTrue(isset($indexes['primary']));
        $this->assertEquals(['product_id', 'category_id'], $indexes['primary']->getColumns());
    }

    /**
     * @test
     */
    public function check_foreign_keys_for_relationship_table()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('product_category', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'product', 'category');
        });

        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('product_category');

        $this->assertCount(2, $foreignKeys);

        // Sort foreign keys by local column name to ensure consistent testing order
        usort($foreignKeys, function ($a, $b) {
            return strcmp($a->getLocalColumns()[0], $b->getLocalColumns()[0]);
        });

        // Check category foreign key
        $this->assertEquals('category_id', $foreignKeys[0]->getLocalColumns()[0]);
        $this->assertEquals('categories', $foreignKeys[0]->getForeignTableName());
        $this->assertEquals('id', $foreignKeys[0]->getForeignColumns()[0]);

        // Check product foreign key
        $this->assertEquals('product_id', $foreignKeys[1]->getLocalColumns()[0]);
        $this->assertEquals('products', $foreignKeys[1]->getForeignTableName());
        $this->assertEquals('id', $foreignKeys[1]->getForeignColumns()[0]);
    }

    /**
     * @test
     */
    public function check_types_of_fields_for_relationship_table()
    {
        Schema::create('product_category', function (Blueprint $table) {
            createDefaultRelationshipTableFields($table, 'product', 'category');
        });

        $columns = Schema::getColumns('product_category');

        $productIdColumn = collect($columns)->firstWhere('name', 'product_id');
        $categoryIdColumn = collect($columns)->firstWhere('name', 'category_id');

        $this->assertEquals('integer', $productIdColumn['type']);
        $this->assertEquals('integer', $categoryIdColumn['type']);
        $this->assertFalse($productIdColumn['nullable']);
        $this->assertFalse($categoryIdColumn['nullable']);
    }
}
