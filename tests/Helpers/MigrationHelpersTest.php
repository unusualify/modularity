<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Tests\TestCase;

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
        $this->assertEquals("'1'", $publishedColumn['default']);
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

        // Foreign keys
        $foreignKeys = Schema::getForeignKeys('product_translations');

        $this->assertCount(1, $foreignKeys);
        $this->assertEquals(['product_id'], $foreignKeys[0]['columns']);
        $this->assertEquals('products', $foreignKeys[0]['foreign_table']);
        $this->assertEquals(['id'], $foreignKeys[0]['foreign_columns']);

        // Indexes
        $indexes = Schema::getIndexes('product_translations');
        $indexNames = array_column($indexes, 'name');

        $this->assertContains('product_id_locale_unique', $indexNames);

        $uniqueIndex = array_filter($indexes, fn ($i) => $i['name'] === 'product_id_locale_unique');
        $uniqueIndex = array_values($uniqueIndex)[0];

        $this->assertTrue($uniqueIndex['unique']);
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
    // public function check_primary_key_constraint_for_relationship_table()
    // {
    //     Schema::create('product_category', function (Blueprint $table) {
    //         createDefaultRelationshipTableFields($table, 'product', 'category');
    //     });

    //     $indexes = Schema::getIndexes('product_category');
    //     $indexNames = array_column($indexes, 'name');

    //     $this->assertContains('primary', $indexNames);
    //     $primaryIndex = array_filter($indexes, fn($i) => $i['name'] === 'primary');
    //     $primaryIndex = array_values($primaryIndex)[0];

    //     $this->assertEquals(['product_id', 'category_id'], $primaryIndex['columns']);
    // }

    // /**
    //  * @test
    //  */
    // public function check_foreign_keys_for_relationship_table()
    // {
    //     Schema::create('products', function (Blueprint $table) {
    //         $table->id();
    //     });

    //     Schema::create('categories', function (Blueprint $table) {
    //         $table->id();
    //     });

    //     Schema::create('product_category', function (Blueprint $table) {
    //         createDefaultRelationshipTableFields($table, 'product', 'category');
    //     });

    //     $foreignKeys = Schema::getForeignKeys('product_category');

    //     $this->assertCount(2, $foreignKeys);

    //     // Sort foreign keys by local column name to ensure consistent testing order
    //     usort($foreignKeys, function ($a, $b) {
    //         return strcmp($a->getLocalColumns()[0], $b->getLocalColumns()[0]);
    //     });

    //     // Check category foreign key
    //     $this->assertEquals('category_id', $foreignKeys[0]->getLocalColumns()[0]);
    //     $this->assertEquals('categories', $foreignKeys[0]->getForeignTableName());
    //     $this->assertEquals('id', $foreignKeys[0]->getForeignColumns()[0]);

    //     // Check product foreign key
    //     $this->assertEquals('product_id', $foreignKeys[1]->getLocalColumns()[0]);
    //     $this->assertEquals('products', $foreignKeys[1]->getForeignTableName());
    //     $this->assertEquals('id', $foreignKeys[1]->getForeignColumns()[0]);
    // }

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

    /**
     * @test
     */
    public function it_creates_default_slugs_table_fields()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('product_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'product');
        });

        $this->assertTrue(Schema::hasColumns('product_slugs', [
            'id',
            'product_id',
            'slug',
            'locale',
            'active',
            'deleted_at',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * @test
     */
    public function it_creates_slugs_table_with_plural_table_name()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('product_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'product', 'products');
        });

        $this->assertTrue(Schema::hasColumn('product_slugs', 'product_id'));
    }

    /**
     * @test
     */
    public function it_creates_slugs_table_with_foreign_key_constraint()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        Schema::create('product_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'product', 'products');
        });

        $foreignKeys = Schema::getForeignKeys('product_slugs');

        $this->assertCount(1, $foreignKeys);
        $this->assertEquals(['product_id'], $foreignKeys[0]['columns']);
        $this->assertEquals('products', $foreignKeys[0]['foreign_table']);
        $this->assertEquals(['id'], $foreignKeys[0]['foreign_columns']);
    }

    /**
     * @test
     */
    public function it_creates_default_revisions_table_fields()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        // Create users table (required for foreign key)
        if (! Schema::hasTable('um_users')) {
            Schema::create('um_users', function (Blueprint $table) {
                $table->id();
            });
        }

        Schema::create('product_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'product');
        });

        $this->assertTrue(Schema::hasColumns('product_revisions', [
            'id',
            'product_id',
            'user_id',
            'payload',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * @test
     */
    public function it_creates_revisions_table_with_plural_table_name()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        if (! Schema::hasTable('um_users')) {
            Schema::create('um_users', function (Blueprint $table) {
                $table->id();
            });
        }

        Schema::create('product_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'product', 'products');
        });

        $this->assertTrue(Schema::hasColumn('product_revisions', 'product_id'));
    }

    /**
     * @test
     */
    public function it_creates_revisions_table_with_foreign_keys()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        if (! Schema::hasTable('um_users')) {
            Schema::create('um_users', function (Blueprint $table) {
                $table->id();
            });
        }

        Schema::create('product_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'product', 'products');
        });

        $foreignKeys = Schema::getForeignKeys('product_revisions');

        $this->assertCount(3, $foreignKeys);

        // Sort for consistent testing
        usort($foreignKeys, fn ($a, $b) => strcmp($a['columns'][0], $b['columns'][0]));

        // Check approved_by foreign key
        $this->assertEquals(['approved_by'], $foreignKeys[0]['columns']);
        $this->assertEquals('um_users', $foreignKeys[0]['foreign_table']);
        $this->assertEquals(['id'], $foreignKeys[0]['foreign_columns']);

        // Check product_id foreign key
        $this->assertEquals(['product_id'], $foreignKeys[1]['columns']);
        $this->assertEquals('products', $foreignKeys[1]['foreign_table']);

        // Check user_id foreign key
        $this->assertEquals(['user_id'], $foreignKeys[2]['columns']);
        $this->assertEquals('um_users', $foreignKeys[2]['foreign_table']);
    }

    /**
     * @test
     */
    public function it_creates_revisions_table_with_json_payload_column()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
        });

        if (! Schema::hasTable('um_users')) {
            Schema::create('um_users', function (Blueprint $table) {
                $table->id();
            });
        }

        Schema::create('product_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'product');
        });

        $columns = Schema::getColumns('product_revisions');
        $payloadColumn = collect($columns)->firstWhere('name', 'payload');

        // SQLiteuses text to store JSON
        $this->assertContains($payloadColumn['type'], ['json', 'text']);
    }
}
