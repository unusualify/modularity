<?php

namespace Unusualify\Modularity\Tests\Models\Traits\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Traits\Core\ChangeRelationships;
use Unusualify\Modularity\Tests\ModelTestCase;

class ChangeRelationshipsTest extends ModelTestCase
{
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_change_relationships_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create test model instance
        $this->model = new class extends Model {
            use ChangeRelationships;

            protected $table = 'test_change_relationships_models';
            protected $fillable = ['name'];
        };

        $this->model->fill(['name' => 'Test Model']);
        $this->model->save();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_change_relationships_models');
        parent::tearDown();
    }

    public function test_trait_initializes_with_empty_changed_relationships()
    {
        $this->assertEquals([], $this->model->getChangedRelationships());
        $this->assertFalse($this->model->wasChangedRelationships());
    }

    public function test_set_changed_relationships()
    {
        $relationships = [
            'users' => ['user1', 'user2'],
            'posts' => ['post1'],
            'tags' => ['tag1', 'tag2', 'tag3']
        ];

        $this->model->setChangedRelationships($relationships);

        $this->assertEquals($relationships, $this->model->getChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships());
    }

    public function test_set_changed_relationships_with_empty_array()
    {
        // First add some relationships
        $this->model->setChangedRelationships(['users' => ['user1']]);
        $this->assertTrue($this->model->wasChangedRelationships());

        // Then set to empty array
        $this->model->setChangedRelationships([]);

        $this->assertEquals([], $this->model->getChangedRelationships());
        $this->assertFalse($this->model->wasChangedRelationships());
    }

    public function test_add_changed_relationships()
    {
        // Add first relationship
        $this->model->addChangedRelationships('users', ['user1', 'user2']);

        $expected = ['users' => ['user1', 'user2']];
        $this->assertEquals($expected, $this->model->getChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships());

        // Add second relationship
        $this->model->addChangedRelationships('posts', ['post1']);

        $expected['posts'] = ['post1'];
        $this->assertEquals($expected, $this->model->getChangedRelationships());
    }

    public function test_add_changed_relationships_overwrites_existing()
    {
        // Add initial relationship
        $this->model->addChangedRelationships('users', ['user1', 'user2']);

        // Overwrite with new data
        $this->model->addChangedRelationships('users', ['user3', 'user4']);

        $expected = ['users' => ['user3', 'user4']];
        $this->assertEquals($expected, $this->model->getChangedRelationships());
    }

    public function test_add_changed_relationships_with_null_value()
    {
        $this->model->addChangedRelationships('users', null);

        $expected = ['users' => null];
        $this->assertEquals($expected, $this->model->getChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships());
    }

    public function test_add_changed_relationships_with_empty_array()
    {
        $this->model->addChangedRelationships('users', []);

        $expected = ['users' => []];
        $this->assertEquals($expected, $this->model->getChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships());
    }

    public function test_get_changed_relationships()
    {
        $relationships = [
            'users' => ['user1', 'user2'],
            'posts' => ['post1'],
            'comments' => []
        ];

        $this->model->setChangedRelationships($relationships);

        $this->assertEquals($relationships, $this->model->getChangedRelationships());
        $this->assertIsArray($this->model->getChangedRelationships());
    }

    public function test_was_changed_relationships_without_arguments()
    {
        // Initially no changes
        $this->assertFalse($this->model->wasChangedRelationships());

        // Add some changes
        $this->model->addChangedRelationships('users', ['user1']);
        $this->assertTrue($this->model->wasChangedRelationships());

        // Clear changes
        $this->model->setChangedRelationships([]);
        $this->assertFalse($this->model->wasChangedRelationships());
    }

    public function test_was_changed_relationships_with_single_relationship()
    {
        $this->model->setChangedRelationships([
            'users' => ['user1'],
            'posts' => ['post1']
        ]);

        // Test existing relationships
        $this->assertTrue($this->model->wasChangedRelationships('users'));
        $this->assertTrue($this->model->wasChangedRelationships('posts'));

        // Test non-existing relationship
        $this->assertFalse($this->model->wasChangedRelationships('comments'));
        $this->assertFalse($this->model->wasChangedRelationships('tags'));
    }

    public function test_was_changed_relationships_with_multiple_relationships_as_array()
    {
        $this->model->setChangedRelationships([
            'users' => ['user1'],
            'posts' => ['post1']
        ]);

        // Test with array of relationships - should return true if ANY exist
        $this->assertTrue($this->model->wasChangedRelationships(['users', 'comments'])); // users exists
        $this->assertTrue($this->model->wasChangedRelationships(['posts', 'tags'])); // posts exists
        $this->assertTrue($this->model->wasChangedRelationships(['users', 'posts'])); // both exist

        // Test with array where none exist
        $this->assertFalse($this->model->wasChangedRelationships(['comments', 'tags']));
    }

    public function test_was_changed_relationships_with_multiple_relationships_as_arguments()
    {
        $this->model->setChangedRelationships([
            'users' => ['user1'],
            'posts' => ['post1']
        ]);

        // Test with multiple arguments - should return true if ANY exist
        $this->assertTrue($this->model->wasChangedRelationships('users', 'comments')); // users exists
        $this->assertTrue($this->model->wasChangedRelationships('posts', 'tags')); // posts exists
        $this->assertTrue($this->model->wasChangedRelationships('users', 'posts')); // both exist

        // Test with arguments where none exist
        $this->assertFalse($this->model->wasChangedRelationships('comments', 'tags'));
    }

    public function test_was_changed_relationships_with_empty_array()
    {
        $this->model->setChangedRelationships([
            'users' => ['user1'],
            'posts' => ['post1']
        ]);

        // Test with empty array should return true (has changes)
        $this->assertTrue($this->model->wasChangedRelationships([]));
    }

    public function test_was_changed_relationships_with_null()
    {
        $this->model->setChangedRelationships([
            'users' => ['user1'],
            'posts' => ['post1']
        ]);

        // Test with null should return false (null is not a valid relationship key)
        $this->assertFalse($this->model->wasChangedRelationships(null));

        // Test with null as a relationship key
        $this->model->addChangedRelationships(null, 'some_value');
        $this->assertTrue($this->model->wasChangedRelationships(null));
    }

    public function test_complex_relationship_data_structures()
    {
        // Test with complex data structures
        $complexRelationships = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ],
            'posts' => [
                'created' => ['post1', 'post2'],
                'updated' => ['post3'],
                'deleted' => []
            ],
            'tags' => 'single_tag',
            'metadata' => [
                'count' => 5,
                'last_updated' => '2023-01-01',
                'nested' => [
                    'deep' => ['very_deep' => 'value']
                ]
            ]
        ];

        $this->model->setChangedRelationships($complexRelationships);

        $this->assertEquals($complexRelationships, $this->model->getChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships('users'));
        $this->assertTrue($this->model->wasChangedRelationships('posts'));
        $this->assertTrue($this->model->wasChangedRelationships('tags'));
        $this->assertTrue($this->model->wasChangedRelationships('metadata'));
        $this->assertFalse($this->model->wasChangedRelationships('nonexistent'));
    }

    public function test_relationship_tracking_workflow()
    {
        // Simulate a typical workflow

        // 1. Initial state - no changes
        $this->assertFalse($this->model->wasChangedRelationships());
        $this->assertEquals([], $this->model->getChangedRelationships());

        // 2. Add some relationships
        $this->model->addChangedRelationships('users', ['user1', 'user2']);
        $this->assertTrue($this->model->wasChangedRelationships());
        $this->assertTrue($this->model->wasChangedRelationships('users'));
        $this->assertFalse($this->model->wasChangedRelationships('posts'));

        // 3. Add more relationships
        $this->model->addChangedRelationships('posts', ['post1']);
        $this->model->addChangedRelationships('tags', ['tag1', 'tag2']);

        $this->assertTrue($this->model->wasChangedRelationships('users'));
        $this->assertTrue($this->model->wasChangedRelationships('posts'));
        $this->assertTrue($this->model->wasChangedRelationships('tags'));
        $this->assertTrue($this->model->wasChangedRelationships(['users', 'posts']));
        $this->assertTrue($this->model->wasChangedRelationships('users', 'posts', 'tags'));

        // 4. Update existing relationship
        $this->model->addChangedRelationships('users', ['user3', 'user4', 'user5']);

        $expected = [
            'users' => ['user3', 'user4', 'user5'],
            'posts' => ['post1'],
            'tags' => ['tag1', 'tag2']
        ];
        $this->assertEquals($expected, $this->model->getChangedRelationships());

        // 5. Clear all changes
        $this->model->setChangedRelationships([]);
        $this->assertFalse($this->model->wasChangedRelationships());
        $this->assertFalse($this->model->wasChangedRelationships('users'));
        $this->assertEquals([], $this->model->getChangedRelationships());
    }

    public function test_edge_cases_and_type_safety()
    {
        // Test with various data types
        $this->model->addChangedRelationships('string_key', 'string_value');
        $this->model->addChangedRelationships('int_key', 123);
        $this->model->addChangedRelationships('float_key', 45.67);
        $this->model->addChangedRelationships('bool_key', true);
        $this->model->addChangedRelationships('null_key', null);
        $this->model->addChangedRelationships('array_key', ['a', 'b', 'c']);
        $this->model->addChangedRelationships('object_key', (object)['prop' => 'value']);

        $changes = $this->model->getChangedRelationships();

        $this->assertEquals('string_value', $changes['string_key']);
        $this->assertEquals(123, $changes['int_key']);
        $this->assertEquals(45.67, $changes['float_key']);
        $this->assertTrue($changes['bool_key']);
        $this->assertNull($changes['null_key']);
        $this->assertEquals(['a', 'b', 'c'], $changes['array_key']);
        $this->assertEquals((object)['prop' => 'value'], $changes['object_key']);

        // Test that all keys are detected as changed
        $this->assertTrue($this->model->wasChangedRelationships('string_key'));
        $this->assertTrue($this->model->wasChangedRelationships('int_key'));
        $this->assertTrue($this->model->wasChangedRelationships('float_key'));
        $this->assertTrue($this->model->wasChangedRelationships('bool_key'));
        $this->assertTrue($this->model->wasChangedRelationships('null_key'));
        $this->assertTrue($this->model->wasChangedRelationships('array_key'));
        $this->assertTrue($this->model->wasChangedRelationships('object_key'));
    }

    public function test_multiple_model_instances_independence()
    {
        // Create another model instance
        $model2 = new class extends Model {
            use ChangeRelationships;

            protected $table = 'test_change_relationships_models';
            protected $fillable = ['name'];
        };
        $model2->fill(['name' => 'Test Model 2']);
        $model2->save();

        // Set different relationships on each model
        $this->model->addChangedRelationships('users', ['user1']);
        $model2->addChangedRelationships('posts', ['post1']);

        // Verify independence
        $this->assertTrue($this->model->wasChangedRelationships('users'));
        $this->assertFalse($this->model->wasChangedRelationships('posts'));

        $this->assertFalse($model2->wasChangedRelationships('users'));
        $this->assertTrue($model2->wasChangedRelationships('posts'));

        $this->assertEquals(['users' => ['user1']], $this->model->getChangedRelationships());
        $this->assertEquals(['posts' => ['post1']], $model2->getChangedRelationships());
    }

    public function test_performance_with_large_datasets()
    {
        // Test with a large number of relationships
        $largeDataset = [];
        for ($i = 0; $i < 100; $i++) {
            $largeDataset["relationship_$i"] = range(1, 50); // 50 items each
        }

        $this->model->setChangedRelationships($largeDataset);

        // Verify all relationships are tracked
        $this->assertTrue($this->model->wasChangedRelationships());
        $this->assertEquals(100, count($this->model->getChangedRelationships()));

        // Test specific relationship checks
        $this->assertTrue($this->model->wasChangedRelationships('relationship_0'));
        $this->assertTrue($this->model->wasChangedRelationships('relationship_50'));
        $this->assertTrue($this->model->wasChangedRelationships('relationship_99'));
        $this->assertFalse($this->model->wasChangedRelationships('relationship_100'));

        // Test multiple relationship checks
        $this->assertTrue($this->model->wasChangedRelationships(['relationship_0', 'relationship_50']));
        $this->assertFalse($this->model->wasChangedRelationships(['nonexistent_1', 'nonexistent_2']));
    }
}
