<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Traits\HasUuid;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasUuidTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table with string primary key
        Schema::create('test_uuid_models', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_model_uses_has_uuid_trait()
    {
        $model = new TestUuidModel;
        $traits = class_uses_recursive($model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasUuid', $traits);
    }

    public function test_boot_has_uuid_generates_ordered_uuid_on_creating()
    {
        $model = new TestUuidModel(['name' => 'Test Model']);

        // Before saving, ID should be null
        $this->assertNull($model->id);

        // Save the model to trigger creating event
        $model->save();

        // After saving, ID should be a UUID string
        $this->assertNotNull($model->id);
        $this->assertIsString($model->id);
        $this->assertTrue(Str::isUuid($model->id));
    }

    public function test_generated_uuid_is_ordered_uuid()
    {
        $model1 = new TestUuidModel(['name' => 'Model 1']);
        $model1->save();

        // Small delay to ensure different timestamp
        usleep(1000);

        $model2 = new TestUuidModel(['name' => 'Model 2']);
        $model2->save();

        // Both should be valid UUIDs
        $this->assertTrue(Str::isUuid($model1->id));
        $this->assertTrue(Str::isUuid($model2->id));

        // They should be different
        $this->assertNotEquals($model1->id, $model2->id);

        // Ordered UUIDs should be sortable (model2 should be "greater" than model1)
        $this->assertGreaterThan($model1->id, $model2->id);
    }

    public function test_get_incrementing_returns_false()
    {
        $model = new TestUuidModel;
        $this->assertFalse($model->getIncrementing());
    }

    public function test_get_key_type_returns_string()
    {
        $model = new TestUuidModel;
        $this->assertEquals('string', $model->getKeyType());
    }

    public function test_uuid_is_set_before_other_creating_events()
    {
        $model = new TestUuidModel(['name' => 'Test Model']);

        // Add a creating event listener to verify UUID is already set
        $uuidSetInEvent = false;
        TestUuidModel::creating(function ($model) use (&$uuidSetInEvent) {
            $uuidSetInEvent = ! empty($model->id) && Str::isUuid($model->id);
        });

        $model->save();

        $this->assertTrue($uuidSetInEvent);
    }

    // public function test_initialize_has_uuid_validates_column_exists()
    // {
    //     // Test with a model that doesn't have the UUID column
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Column id not found in');

    //     // Create table without the UUID column
    //     Schema::create('test_invalid_uuid_models', function (Blueprint $table) {
    //         $table->increments('invalid_id');
    //         $table->string('name');
    //         $table->timestamps();
    //     });

    //     $model = new TestInvalidUuidModel();
    //     $model->initializeHasUuid();
    // }

    // public function test_initialize_has_uuid_validates_column_type()
    // {
    //     // Test with a model that has wrong column type for UUID
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Column "id" is not proper, because the column type is "integer"');

    //     // Create table with integer ID instead of string
    //     Schema::create('test_wrong_type_uuid_models', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->string('name');
    //         $table->timestamps();
    //     });

    //     $model = new TestWrongTypeUuidModel();
    //     $model->initializeHasUuid();
    // }

    public function test_get_uuid_column_returns_default()
    {
        $model = new TestUuidModel;
        $this->assertEquals('id', $model::getUuidColumn());
    }

    public function test_get_uuid_column_returns_custom_column()
    {
        Schema::create('test_custom_uuid_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('name');
            $table->timestamps();
        });

        $model = new TestCustomUuidModel;
        $this->assertEquals('uuid', $model::getUuidColumn());
    }

    public function test_custom_uuid_column_works()
    {
        // Create table with custom UUID column
        Schema::create('test_custom_uuid_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('name');
            $table->timestamps();
        });

        $model = new TestCustomUuidModel(['name' => 'Test Model']);
        $model->save();

        $this->assertNotNull($model->uuid);
        $this->assertTrue(Str::isUuid($model->uuid));
        $this->assertNull($model->id); // Regular ID should remain null
    }

    public function test_can_find_model_by_uuid()
    {
        $model = new TestUuidModel(['name' => 'Test Model']);
        $model->save();

        $foundModel = TestUuidModel::find($model->id);

        $this->assertNotNull($foundModel);
        $this->assertEquals($model->id, $foundModel->id);
        $this->assertEquals($model->name, $foundModel->name);
    }

    public function test_model_can_be_saved_and_retrieved()
    {
        $model = new TestUuidModel(['name' => 'Test Model']);
        $model->save();

        $this->assertDatabaseHas('test_uuid_models', [
            'id' => $model->id,
            'name' => 'Test Model',
        ]);
    }

    public function test_uuid_is_not_overwritten_if_already_set()
    {
        $customUuid = (string) Str::orderedUuid();
        $model = new TestUuidModel(['name' => 'Test Model']);
        $model->id = $customUuid;

        $model->save();

        $this->assertEquals($customUuid, $model->id);
    }

    public function test_multiple_models_have_unique_uuids()
    {
        $models = [];
        $uuids = [];

        // Create multiple models
        for ($i = 0; $i < 10; $i++) {
            $model = new TestUuidModel(['name' => "Model $i"]);
            $model->save();
            $models[] = $model;
            $uuids[] = $model->id;
        }

        // All UUIDs should be unique
        $this->assertEquals(count($uuids), count(array_unique($uuids)));

        // All should be valid UUIDs
        foreach ($uuids as $uuid) {
            $this->assertTrue(Str::isUuid($uuid));
        }
    }

    public function test_trait_uses_manage_eloquent()
    {
        $model = new TestUuidModel;
        $traits = class_uses_recursive($model);

        // Verify that HasUuid uses ManageEloquent trait
        $this->assertContains('Oobook\Database\Eloquent\Concerns\ManageEloquent', $traits);
    }

    public function test_uuid_only_generated_on_creating_not_updating()
    {
        // Create and save model
        $model = new TestUuidModel(['name' => 'Original Name']);
        $model->save();
        $originalId = $model->id;

        // Update the model
        $model->name = 'Updated Name';
        $model->save();

        // UUID should remain the same after update
        $this->assertEquals($originalId, $model->id);
        $this->assertEquals('Updated Name', $model->name);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_uuid_models');
        Schema::dropIfExists('test_invalid_uuid_models');
        Schema::dropIfExists('test_wrong_type_uuid_models');
        Schema::dropIfExists('test_custom_uuid_models');
        parent::tearDown();
    }
}

// Test model that uses the HasUuid trait
class TestUuidModel extends Model
{
    use HasUuid;

    protected $table = 'test_uuid_models';

    protected $fillable = ['name'];

    // Override the key type to string for UUID
    protected $keyType = 'string';

    public $incrementing = false;
}

// Test model with invalid table structure (missing UUID column)
class TestInvalidUuidModel extends Model
{
    use HasUuid;

    protected $table = 'test_invalid_uuid_models';

    protected $fillable = ['name'];
}

// Test model with wrong column type for UUID
class TestWrongTypeUuidModel extends Model
{
    use HasUuid;

    protected $table = 'test_wrong_type_uuid_models';

    protected $fillable = ['name'];
}

// Test model with custom UUID column
class TestCustomUuidModel extends Model
{
    use HasUuid;

    protected $table = 'test_custom_uuid_models';

    protected $fillable = ['name'];

    protected static $uuidColumn = 'uuid';
}
