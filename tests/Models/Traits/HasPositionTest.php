<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Traits\HasPosition;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasPositionTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_position_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function test_model_uses_has_position_trait()
    {
        $model = new TestPositionModel;
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasPosition', $traits);
    }

    public function test_boot_has_position_sets_position_on_creating()
    {
        $model = new TestPositionModel(['name' => 'Test Model']);

        // Before saving, position should be 0 (default)
        $this->assertEquals(0, $model->position);

        // Save the model to trigger creating event
        $model->save();

        // After saving, position should be set to 1 (first item)
        $this->assertEquals(1, $model->position);
    }

    public function test_set_to_last_position_increments_from_existing_positions()
    {
        // Create first model
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save();
        $this->assertEquals(1, $model1->position);

        // Create second model
        $model2 = new TestPositionModel(['name' => 'Model 2']);
        $model2->save();
        $this->assertEquals(2, $model2->position);

        // Create third model
        $model3 = new TestPositionModel(['name' => 'Model 3']);
        $model3->save();
        $this->assertEquals(3, $model3->position);
    }

    public function test_get_current_last_position_returns_highest_position()
    {
        // Create models with different positions
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save(); // position = 1

        $model2 = new TestPositionModel(['name' => 'Model 2']);
        $model2->save(); // position = 2

        $model3 = new TestPositionModel(['name' => 'Model 3']);
        $model3->save(); // position = 3

        // Test the protected method through reflection
        $reflectionMethod = new \ReflectionMethod($model1, 'getCurrentLastPosition');
        $reflectionMethod->setAccessible(true);
        $lastPosition = $reflectionMethod->invoke($model1);

        $this->assertEquals(3, $lastPosition);
    }

    public function test_get_current_last_position_returns_zero_when_no_records()
    {
        $model = new TestPositionModel(['name' => 'Test Model']);

        // Test the protected method through reflection
        $reflectionMethod = new \ReflectionMethod($model, 'getCurrentLastPosition');
        $reflectionMethod->setAccessible(true);
        $lastPosition = $reflectionMethod->invoke($model);

        $this->assertEquals(0, $lastPosition);
    }

    public function test_scope_ordered_orders_by_position()
    {
        // Create models in random order
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->position = 1;
        $model1->save();

        $model3 = new TestPositionModel(['name' => 'Model 3']);
        $model3->position = 2;
        $model3->save();

        $model2 = new TestPositionModel(['name' => 'Model 2']);
        $model2->position = 2;
        $model2->save();

        // Query with ordered scope
        $orderedModels = TestPositionModel::ordered()->get();

        $this->assertEquals('Model 1', $orderedModels->first()->name);
        $this->assertEquals('Model 2', $orderedModels->get(1)->name);
        $this->assertEquals('Model 3', $orderedModels->last()->name);

        // Verify positions are in ascending order
        $positions = $orderedModels->pluck('position')->toArray();
        $this->assertEquals([1, 2, 3], $positions);
    }

    public function test_set_new_order_reorders_models_by_ids()
    {
        // Create models
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save();

        $model2 = new TestPositionModel(['name' => 'Model 2']);
        $model2->save();

        $model3 = new TestPositionModel(['name' => 'Model 3']);
        $model3->save();

        // Reorder: model3, model1, model2
        $newOrder = [$model3->id, $model1->id, $model2->id];
        TestPositionModel::setNewOrder($newOrder);

        // Refresh models from database
        $model1->refresh();
        $model2->refresh();
        $model3->refresh();

        $this->assertEquals(2, $model1->position); // model1 is now second
        $this->assertEquals(3, $model2->position); // model2 is now third
        $this->assertEquals(1, $model3->position); // model3 is now first
    }

    public function test_set_new_order_with_custom_start_order()
    {
        // Create models
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save();

        $model2 = new TestPositionModel(['name' => 'Model 2']);
        $model2->save();

        // Reorder starting from position 5
        $newOrder = [$model2->id, $model1->id];
        TestPositionModel::setNewOrder($newOrder, 5);

        // Refresh models from database
        $model1->refresh();
        $model2->refresh();

        $this->assertEquals(6, $model1->position); // model1 gets position 6 (5+1)
        $this->assertEquals(5, $model2->position); // model2 gets position 5
    }

    public function test_set_new_order_throws_exception_for_non_array_input()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must pass an array to setNewOrder');

        TestPositionModel::setNewOrder('not-an-array');
    }

    public function test_set_new_order_returns_one()
    {
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save();

        $result = TestPositionModel::setNewOrder([$model1->id]);

        $this->assertEquals(1, $result);
    }

    public function test_set_new_order_handles_empty_array()
    {
        $result = TestPositionModel::setNewOrder([]);

        $this->assertEquals(1, $result);
    }

    public function test_set_new_order_ignores_non_existent_ids()
    {
        $model1 = new TestPositionModel(['name' => 'Model 1']);
        $model1->save();

        // Include a non-existent ID - this should not cause errors
        // but might cause issues depending on implementation
        $newOrder = [$model1->id, 999999]; // 999999 doesn't exist

        // This test might need to be adjusted based on actual behavior
        // If the method throws an exception for non-existent IDs, we should test for that
        try {
            TestPositionModel::setNewOrder($newOrder);
            $model1->refresh();
            $this->assertEquals(1, $model1->position);
        } catch (\Exception $e) {
            // If it throws an exception, that's also valid behavior
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\ModelNotFoundException::class, $e);
        }
    }

    public function test_position_persists_after_save()
    {
        $model = new TestPositionModel(['name' => 'Test Model']);
        $model->save();

        $savedPosition = $model->position;

        // Retrieve fresh from database
        $retrievedModel = TestPositionModel::find($model->id);

        $this->assertEquals($savedPosition, $retrievedModel->position);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_position_models');
        parent::tearDown();
    }
}

// Test model that uses the HasPosition trait
class TestPositionModel extends Model
{
    use HasPosition;

    protected $table = 'test_position_models';

    protected $fillable = ['name', 'position'];
}
