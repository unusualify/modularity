<?php

namespace Unusualify\Modularity\Tests\Models\Traits\Core;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Traits\Core\HasScopes;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasScopesTest extends ModelTestCase
{
    use RefreshDatabase;

    protected $model;
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table with all necessary columns
        Schema::create('test_has_scopes_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('published')->default(false);
            $table->boolean('public')->default(false);
            $table->datetime('publish_start_date')->nullable();
            $table->datetime('publish_end_date')->nullable();
            $table->string('category')->nullable();
            $table->integer('priority')->nullable();
            $table->timestamps();
        });

        // Create anonymous test model class
        $this->testModel = new class extends Model {
            use HasScopes;

            protected $table = 'test_has_scopes_models';
            protected $fillable = [
                'name', 'published', 'public', 'publish_start_date',
                'publish_end_date', 'category', 'priority'
            ];

            protected $casts = [
                'published' => 'boolean',
                'public' => 'boolean',
                'publish_start_date' => 'datetime',
                'publish_end_date' => 'datetime',
            ];

            // Custom scope for testing
            public function scopeHighPriority($query)
            {
                return $query->where('priority', '>', 5);
            }

            public function scopeByCategory($query, $category)
            {
                return $query->where('category', $category);
            }
        };

        $this->model = $this->testModel;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_has_scopes_models');
        parent::tearDown();
    }

    public function test_has_scope_with_existing_model_scope()
    {
        $this->assertTrue($this->testModel::hasScope('published'));
        $this->assertTrue($this->testModel::hasScope('draft'));
        $this->assertTrue($this->testModel::hasScope('visible'));
        $this->assertTrue($this->testModel::hasScope('publishedInListings'));
        $this->assertTrue($this->testModel::hasScope('between'));
        $this->assertTrue($this->testModel::hasScope('createdAtBetween'));
        $this->assertTrue($this->testModel::hasScope('updatedAtBetween'));
        $this->assertTrue($this->testModel::hasScope('highPriority'));
        $this->assertTrue($this->testModel::hasScope('byCategory'));
    }

    public function test_has_scope_with_non_existing_scope()
    {
        $this->assertFalse($this->testModel::hasScope('nonExistentScope'));
        $this->assertFalse($this->testModel::hasScope('invalidScope'));
        $this->assertFalse($this->testModel::hasScope(''));
    }

    public function test_has_scope_with_builder_methods()
    {
        // Test some common Builder methods that should return true
        $this->assertTrue($this->testModel::hasScope('where'));
        $this->assertTrue($this->testModel::hasScope('orWhere'));
        $this->assertTrue($this->testModel::hasScope('whereNot'));
        $this->assertTrue($this->testModel::hasScope('orWhereNot'));
    }

    public function test_scope_published()
    {
        // Create test data
        $publishedModel = $this->testModel::create([
            'name' => 'Published Model',
            'published' => true,
        ]);

        $draftModel = $this->testModel::create([
            'name' => 'Draft Model',
            'published' => false,
        ]);

        // Test published scope
        $publishedModels = $this->testModel::published()->get();

        $this->assertCount(1, $publishedModels);
        $this->assertEquals($publishedModel->id, $publishedModels->first()->id);
        $this->assertTrue($publishedModels->first()->published);
    }

    public function test_scope_draft()
    {
        // Create test data
        $publishedModel = $this->testModel::create([
            'name' => 'Published Model',
            'published' => true,
        ]);

        $draftModel = $this->testModel::create([
            'name' => 'Draft Model',
            'published' => false,
        ]);

        // Test draft scope
        $draftModels = $this->testModel::draft()->get();

        $this->assertCount(1, $draftModels);
        $this->assertEquals($draftModel->id, $draftModels->first()->id);
        $this->assertFalse($draftModels->first()->published);
    }

    public function test_scope_published_in_listings()
    {
        // Create test data
        $publicPublishedModel = $this->testModel::create([
            'name' => 'Public Published Model',
            'published' => true,
            'public' => true,
        ]);

        $privatePublishedModel = $this->testModel::create([
            'name' => 'Private Published Model',
            'published' => true,
            'public' => false,
        ]);

        $draftModel = $this->testModel::create([
            'name' => 'Draft Model',
            'published' => false,
            'public' => true,
        ]);

        // Test publishedInListings scope
        $listingModels = $this->testModel::publishedInListings()->get();

        $this->assertCount(1, $listingModels);
        $this->assertEquals($publicPublishedModel->id, $listingModels->first()->id);
        $this->assertTrue($listingModels->first()->published);
        $this->assertTrue($listingModels->first()->public);
    }

    public function test_scope_visible_with_publish_dates()
    {
        $now = Carbon::now();
        $past = $now->copy()->subDays(5);
        $future = $now->copy()->addDays(5);

        // Create test data
        $currentlyVisibleModel = $this->testModel::create([
            'name' => 'Currently Visible',
            'publish_start_date' => $past,
            'publish_end_date' => $future,
        ]);

        $notYetVisibleModel = $this->testModel::create([
            'name' => 'Not Yet Visible',
            'publish_start_date' => $future,
            'publish_end_date' => $future->copy()->addDays(5),
        ]);

        $noLongerVisibleModel = $this->testModel::create([
            'name' => 'No Longer Visible',
            'publish_start_date' => $past->copy()->subDays(10),
            'publish_end_date' => $past,
        ]);

        $alwaysVisibleModel = $this->testModel::create([
            'name' => 'Always Visible',
            'publish_start_date' => null,
            'publish_end_date' => null,
        ]);

        // Test visible scope
        $visibleModels = $this->testModel::visible()->get();

        $this->assertGreaterThanOrEqual(2, $visibleModels->count());
        $this->assertTrue($visibleModels->contains('id', $currentlyVisibleModel->id));
        $this->assertTrue($visibleModels->contains('id', $alwaysVisibleModel->id));
        $this->assertFalse($visibleModels->contains('id', $notYetVisibleModel->id));
        $this->assertFalse($visibleModels->contains('id', $noLongerVisibleModel->id));
    }

    public function test_scope_between()
    {
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now()->subDays(5);
        $betweenDate = Carbon::now()->subDays(7);
        $outsideDate = Carbon::now()->subDays(2);

        // Create test data
        $modelInRange = $this->testModel::create([
            'name' => 'In Range',
        ]);

        $modelInRange->created_at = $betweenDate;
        $modelInRange->save();

        $modelOutOfRange = $this->testModel::create([
            'name' => 'Out of Range',
        ]);

        $modelOutOfRange->created_at = $outsideDate;
        $modelOutOfRange->save();

        // Test between scope
        $modelsInRange = $this->testModel::between('created_at', $startDate, $endDate)->get();

        $this->assertCount(1, $modelsInRange);
        $this->assertEquals($modelInRange->id, $modelsInRange->first()->id);
    }

    public function test_scope_between_with_null_dates()
    {
        // Create test data
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Test between scope with null dates (should return all records)
        $allModels = $this->testModel::between('created_at', null, null)->get();
        $this->assertCount(1, $allModels);

        // Test with only start date null
        $allModels = $this->testModel::between('created_at', null, Carbon::now())->get();
        $this->assertCount(1, $allModels);

        // Test with only end date null
        $allModels = $this->testModel::between('created_at', Carbon::now()->subDay(), null)->get();
        $this->assertCount(1, $allModels);
    }

    public function test_scope_created_at_between()
    {
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now()->subDays(5);
        $betweenDate = Carbon::now()->subDays(7);

        // Create test data with specific created_at
        $modelInRange = $this->testModel::create([
            'name' => 'In Range',
        ]);

        $modelInRange->created_at = $betweenDate;
        $modelInRange->save();

        $modelOutOfRange = $this->testModel::create([
            'name' => 'Out of Range',
        ]);

        $modelOutOfRange->created_at = Carbon::now()->subDays(2);
        $modelOutOfRange->save();

        // Test createdAtBetween scope
        $modelsInRange = $this->testModel::createdAtBetween($startDate, $endDate)->get();

        $this->assertCount(1, $modelsInRange);
        $this->assertEquals($modelInRange->id, $modelsInRange->first()->id);
    }

    public function test_scope_updated_at_between()
    {
        $startDate = Carbon::now()->subDays(10);
        $endDate = Carbon::now()->subDays(5);
        $betweenDate = Carbon::now()->subDays(7);

        // Create test data
        $model = $this->testModel::create(['name' => 'Test Model']);

        // Update the model with a specific updated_at time
        $model->updated_at = $betweenDate;
        $model->save();

        $modelOutOfRange = $this->testModel::create(['name' => 'Out of Range']);

        // Test updatedAtBetween scope
        $modelsInRange = $this->testModel::updatedAtBetween($startDate, $endDate)->get();

        $this->assertCount(1, $modelsInRange);
        $this->assertEquals($model->id, $modelsInRange->first()->id);
    }

    public function test_handle_scopes_with_except_ids()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Model 1']);
        $model2 = $this->testModel::create(['name' => 'Model 2']);
        $model3 = $this->testModel::create(['name' => 'Model 3']);

        $query = $this->testModel::query();
        $scopes = ['exceptIds' => [$model1->id, $model2->id]];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($model3->id, $result->first()->id);
    }

    public function test_handle_scopes_with_method_scopes()
    {
        // Create test data
        $highPriorityModel = $this->testModel::create([
            'name' => 'High Priority',
            'priority' => 10,
        ]);

        $lowPriorityModel = $this->testModel::create([
            'name' => 'Low Priority',
            'priority' => 3,
        ]);

        $query = $this->testModel::query();
        $scopes = ['high_priority' => true];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($highPriorityModel->id, $result->first()->id);
    }

    public function test_handle_scopes_with_method_scopes_with_parameters()
    {
        // Create test data
        $techModel = $this->testModel::create([
            'name' => 'Tech Article',
            'category' => 'technology',
        ]);

        $sportsModel = $this->testModel::create([
            'name' => 'Sports Article',
            'category' => 'sports',
        ]);

        $query = $this->testModel::query();
        $scopes = ['by_category' => 'technology'];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($techModel->id, $result->first()->id);
    }

    public function test_handle_scopes_with_string_method_scopes()
    {
        // Create test data
        $publishedModel = $this->testModel::create([
            'name' => 'Published',
            'published' => true,
        ]);

        $draftModel = $this->testModel::create([
            'name' => 'Draft',
            'published' => false,
        ]);

        $query = $this->testModel::query();
        $scopes = ['status' => 'published'];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($publishedModel->id, $result->first()->id);
    }

    public function test_handle_scopes_with_array_values()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Model 1', 'category' => 'tech']);
        $model2 = $this->testModel::create(['name' => 'Model 2', 'category' => 'sports']);
        $model3 = $this->testModel::create(['name' => 'Model 3', 'category' => 'news']);

        $query = $this->testModel::query();
        $scopes = ['category' => ['tech', 'sports']];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $model1->id));
        $this->assertTrue($result->contains('id', $model2->id));
        $this->assertFalse($result->contains('id', $model3->id));
    }

    public function test_handle_scopes_with_like_operator()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Technology Article']);
        $model2 = $this->testModel::create(['name' => 'Sports News']);
        $model3 = $this->testModel::create(['name' => 'Tech Review']);

        $query = $this->testModel::query();
        $scopes = ['%name' => 'Tech'];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $model1->id));
        $this->assertTrue($result->contains('id', $model3->id));
        $this->assertFalse($result->contains('id', $model2->id));
    }

    public function test_handle_scopes_with_not_like_operator()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Technology Article']);
        $model2 = $this->testModel::create(['name' => 'Sports News']);

        $query = $this->testModel::query();
        $scopes = ['%name' => '!Sports'];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model1->id));
        $this->assertFalse($result->contains('id', $model2->id));
    }

    public function test_handle_scopes_with_not_equal_operator()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Model 1', 'category' => 'tech']);
        $model2 = $this->testModel::create(['name' => 'Model 2', 'category' => 'sports']);

        $query = $this->testModel::query();
        $scopes = ['category' => '!tech'];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model2->id));
        $this->assertFalse($result->contains('id', $model1->id));
    }

    public function test_handle_scopes_with_exact_match()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Model 1', 'priority' => 5]);
        $model2 = $this->testModel::create(['name' => 'Model 2', 'priority' => 10]);

        $query = $this->testModel::query();
        $scopes = ['priority' => 5];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model1->id));
        $this->assertFalse($result->contains('id', $model2->id));
    }

    public function test_handle_scopes_with_empty_string_value()
    {
        // Create test data
        $model1 = $this->testModel::create(['name' => 'Model 1', 'category' => 'tech']);
        $model2 = $this->testModel::create(['name' => 'Model 2', 'category' => '']);

        $query = $this->testModel::query();
        $scopes = ['category' => ''];

        // Empty string should not filter anything
        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(2, $result);
    }

    public function test_handle_scopes_with_multiple_scopes()
    {
        // Create test data
        $model1 = $this->testModel::create([
            'name' => 'Published Tech',
            'published' => true,
            'category' => 'tech',
        ]);

        $model2 = $this->testModel::create([
            'name' => 'Draft Tech',
            'published' => false,
            'category' => 'tech',
        ]);

        $model3 = $this->testModel::create([
            'name' => 'Published Sports',
            'published' => true,
            'category' => 'sports',
        ]);

        $query = $this->testModel::query();
        $scopes = [
            'published' => true,
            'category' => 'tech',
        ];

        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model1->id));
        $this->assertFalse($result->contains('id', $model2->id));
        $this->assertFalse($result->contains('id', $model3->id));
    }

    public function test_handle_scopes_with_database_driver_detection()
    {
        // This test verifies that the method correctly detects the database driver
        // and sets the appropriate LIKE operator

        $currentDriver = DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        // Create test data
        $model = $this->testModel::create(['name' => 'Test Model']);

        $query = $this->testModel::query();
        $scopes = ['%name' => 'Test'];

        // The method should work regardless of driver
        $result = $this->testModel::handleScopes($query, $scopes)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model->id));
    }

    public function test_scope_chaining()
    {
        // Create test data
        $model = $this->testModel::create([
            'name' => 'Published Tech Article',
            'published' => true,
            'category' => 'tech',
            'priority' => 10,
        ]);

        $draftModel = $this->testModel::create([
            'name' => 'Draft Article',
            'published' => false,
            'category' => 'tech',
            'priority' => 10,
        ]);

        // Test chaining multiple scopes
        $result = $this->testModel::published()
            ->byCategory('tech')
            ->highPriority()
            ->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $model->id));
        $this->assertFalse($result->contains('id', $draftModel->id));
    }

    public function test_scope_edge_cases()
    {
        // Test with model that doesn't have public column
        $modelWithoutPublic = new class extends Model {
            use HasScopes;

            protected $table = 'test_has_scopes_models';
            protected $fillable = ['name', 'published'];

            protected $casts = [
                'published' => 'boolean',
            ];
        };

        // Create test data
        $model = $modelWithoutPublic::create([
            'name' => 'Test Model',
            'published' => true,
        ]);

        // publishedInListings should work even without public column
        $result = $modelWithoutPublic::publishedInListings()->get();
        $this->assertCount(1, $result);
    }
}
