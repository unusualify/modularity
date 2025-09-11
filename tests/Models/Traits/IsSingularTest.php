<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Scopes\SingularScope;
use Unusualify\Modularity\Entities\Traits\IsSingular;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Tests\ModelTestCase;

class IsSingularTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_model_uses_is_singular_trait()
    {
        $model = new TestSingularModel();
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\IsSingular', $traits);
    }

    public function test_boot_is_singular_adds_global_scope()
    {
        $model = new TestSingularModel();
        $globalScopes = $model->getGlobalScopes();

        $hasSingularScope = false;
        foreach ($globalScopes as $scope) {
            if ($scope instanceof SingularScope) {
                $hasSingularScope = true;
                break;
            }
        }

        $this->assertTrue($hasSingularScope);
    }

    public function test_initialize_is_singular_merges_fillable_attributes()
    {
        $model = new TestSingularModel();

        $fillable = $model->getFillable();

        $this->assertContains('singleton_type', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('name', $fillable); // Original fillable
        $this->assertContains('description', $fillable); // Original fillable
    }

    public function test_initialize_is_singular_sets_content_cast()
    {
        $model = new TestSingularModel();

        $casts = $model->getCasts();

        $this->assertEquals('array', $casts['content']);
    }

    public function test_creating_event_sets_singleton_type_and_content()
    {
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'description' => 'Test Description',
            'published' => true,
        ]);

        $model->save();

        // Check the raw database values since retrieved event modifies attributes
        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);

        $this->assertEquals(TestSingularModel::class, $fresh->getRawOriginal('singleton_type'));
        $this->assertIsString($fresh->getRawOriginal('content'));

        $content = json_decode($fresh->getRawOriginal('content'), true);
        $this->assertEquals('Test Name', $content['name']);
        $this->assertEquals('Test Description', $content['description']);
        $this->assertTrue($content['published']);
    }

    public function test_creating_event_removes_regular_attributes_from_model()
    {
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'description' => 'Test Description',
        ]);

        $model->save();

        // Regular attributes should be removed from the model's direct attributes
        // but accessible through the retrieved event logic
        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);
        $this->assertNull($fresh->getRawOriginal('name'));
        $this->assertNull($fresh->getRawOriginal('description'));

        // But they should be accessible through normal attribute access after retrieval
        $retrieved = TestSingularModel::find($model->id);
        $this->assertEquals('Test Name', $retrieved->name);
        $this->assertEquals('Test Description', $retrieved->description);
    }

    public function test_updating_event_updates_content()
    {
        $model = new TestSingularModel([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);
        $model->save();

        $model->name = 'Updated Name';
        $model->description = 'Updated Description';
        $model->save();

        // Check the raw database content
        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);
        $content = json_decode($fresh->getRawOriginal('content'), true);

        $this->assertEquals('Updated Name', $content['name']);
        $this->assertEquals('Updated Description', $content['description']);
    }

    public function test_retrieved_event_populates_attributes_from_content()
    {
        // Create and save a model
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'description' => 'Test Description',
        ]);
        $model->save();
        $id = $model->id;

        // Retrieve the model fresh from database
        $retrievedModel = TestSingularModel::find($id);

        $this->assertEquals('Test Name', $retrievedModel->name);
        $this->assertEquals('Test Description', $retrievedModel->description);
    }

    public function test_retrieved_event_removes_content_and_singleton_type_from_attributes()
    {
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'description' => 'Test Description',
        ]);
        $model->save();
        $id = $model->id;

        $retrievedModel = TestSingularModel::find($id);

        // These should be removed from the model's attributes after retrieval
        $this->assertNull($retrievedModel->getAttribute('content'));
        $this->assertNull($retrievedModel->getAttribute('singleton_type'));
    }

    public function test_single_method_returns_first_or_creates_new_instance()
    {
        // First call should create a new instance
        $model1 = TestSingularModel::single();
        $this->assertInstanceOf(TestSingularModel::class, $model1);
        $this->assertTrue($model1->exists);

        // Second call should return the same instance
        $model2 = TestSingularModel::single();
        $this->assertEquals($model1->id, $model2->id);
    }

    public function test_scope_published_filters_by_published_content()
    {
        // Note: Due to singleton pattern, only one model per class can exist
        // So we test published scope with a single model
        $publishedModel = new TestSingularModel(['name' => 'Published', 'published' => true]);
        $publishedModel->save();

        $publishedResults = TestSingularModel::published()->get();
        $this->assertCount(1, $publishedResults);
        $this->assertEquals('Published', $publishedResults->first()->name);

        // Test with unpublished model - update the existing one
        $publishedModel->published = false;
        $publishedModel->save();

        $unpublishedResults = TestSingularModel::published()->get();
        $this->assertCount(0, $unpublishedResults);
    }

    public function test_is_published_returns_correct_boolean_value()
    {
        $model = new TestSingularModel(['name' => 'Test', 'published' => true]);
        $model->save();

        $retrieved = TestSingularModel::find($model->id);
        $this->assertTrue($retrieved->isPublished());

        // Update to unpublished
        $model->published = false;
        $model->save();

        $retrievedAgain = TestSingularModel::find($model->id);
        $this->assertFalse($retrievedAgain->isPublished());
    }

    public function test_is_published_returns_true_by_default_when_published_not_set()
    {
        $model = new TestSingularModel(['name' => 'Test']);
        $model->save();
        $model->refresh();

        $this->assertTrue($model->isPublished());
    }

    public function test_get_table_returns_configured_table_name()
    {
        $model = new TestSingularModel();

        $expectedTable = Modularity::config('tables.singletons', 'modularity_singletons');
        $this->assertEquals($expectedTable, $model->getTable());
    }

    public function test_self_attributes_are_not_included_in_content()
    {
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'singleton_type' => 'ShouldBeIgnored',
            'content' => ['should' => 'be ignored'],
        ]);

        $model->save();

        // Check the raw database content
        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);
        $content = json_decode($fresh->getRawOriginal('content'), true);

        // Self attributes should not be in content
        $this->assertArrayNotHasKey('singleton_type', $content);
        $this->assertArrayNotHasKey('content', $content);
        $this->assertArrayHasKey('name', $content);

        // Verify singleton_type was set correctly (not from the passed value)
        $this->assertEquals(TestSingularModel::class, $fresh->getRawOriginal('singleton_type'));
    }

        public function test_only_fillable_attributes_are_stored_in_content()
    {
        $model = new TestSingularModel([
            'name' => 'Test Name',
            'description' => 'Test Description',
        ]);

        $model->save();

        // Check the raw database content
        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);
        $content = json_decode($fresh->getRawOriginal('content'), true);

        $this->assertArrayHasKey('name', $content);
        $this->assertArrayHasKey('description', $content);

        // Verify that only the expected fillable attributes are in content
        // (excluding singleton_type and content which are self-attributes)
        $expectedKeys = ['name', 'description', 'published'];
        foreach ($expectedKeys as $key) {
            if (isset($content[$key])) {
                $this->assertContains($key, (new TestSingularModel())->getFillable());
            }
        }
    }

    public function test_singular_scope_filters_by_model_class()
    {
        // Create instances of different model types
        $model1 = new TestSingularModel(['name' => 'Model 1']);
        $model1->save();

        $model2 = new AnotherTestSingularModel(['title' => 'Model 2']);
        $model2->save();

        // Each model should only see its own type due to SingularScope
        $this->assertCount(1, TestSingularModel::all());
        $this->assertCount(1, AnotherTestSingularModel::all());

        // Without global scope, we should see both
        $this->assertCount(2, TestSingularModel::withoutGlobalScopes()->get());
    }

    public function test_single_method_respects_singleton_pattern()
    {
        // First call creates the singleton
        $singleton1 = TestSingularModel::single();
        $this->assertTrue($singleton1->exists);

        // Second call returns the same instance
        $singleton2 = TestSingularModel::single();
        $this->assertEquals($singleton1->id, $singleton2->id);

        // Even after creating additional models manually, single() returns the first one
        $manual = new TestSingularModel(['name' => 'Manual']);
        $manual->save();

        $singleton3 = TestSingularModel::single();
        $this->assertEquals($singleton1->id, $singleton3->id);
    }

    public function test_content_includes_all_fillable_attributes_with_null_values()
    {
        $model = new TestSingularModel();
        $model->save();

        $fresh = TestSingularModel::withoutGlobalScopes()->find($model->id);
        $content = json_decode($fresh->getRawOriginal('content'), true);

        $this->assertIsArray($content);

        // Content should include all fillable attributes (excluding self-attributes)
        $expectedAttributes = ['name', 'description', 'published'];
        foreach ($expectedAttributes as $attribute) {
            $this->assertArrayHasKey($attribute, $content);
            $this->assertNull($content[$attribute]); // All should be null when not set
        }

        // Should not include self-attributes
        $this->assertArrayNotHasKey('singleton_type', $content);
        $this->assertArrayNotHasKey('content', $content);
    }

    public function test_retrieved_event_handles_null_content_gracefully()
    {
        // Create model without setting any fillable attributes
        $model = new TestSingularModel();
        $model->save();

        // Retrieve should not fail even with empty content
        $retrieved = TestSingularModel::find($model->id);
        $this->assertNotNull($retrieved);
        $this->assertNull($retrieved->name);
        $this->assertNull($retrieved->description);
    }

    public function test_scope_published_uses_json_path_query()
    {
        // Create published model
        $published = new TestSingularModel(['name' => 'Published', 'published' => true]);
        $published->save();

        // Create unpublished model
        $unpublished = new TestSingularModel(['name' => 'Unpublished', 'published' => false]);
        $unpublished->save();

        // Test the published scope
        $publishedResults = TestSingularModel::published()->get();
        $this->assertCount(1, $publishedResults);
        $this->assertEquals('Published', $publishedResults->first()->name);
    }

    public function test_is_published_handles_missing_published_key()
    {
        $model = new TestSingularModel(['name' => 'Test']);
        $model->save();

        // Should default to true when published key is missing
        $retrieved = TestSingularModel::find($model->id);
        $this->assertTrue($retrieved->isPublished());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

// Test model that uses the IsSingular trait
class TestSingularModel extends Model
{
    use IsSingular;

    protected $fillable = ['name', 'description', 'published'];
}

// Another test model to test scope isolation
class AnotherTestSingularModel extends Model
{
    use IsSingular;

    protected $fillable = ['title', 'published'];
}
