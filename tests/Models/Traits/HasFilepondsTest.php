<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Filepond;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasFilepondsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected TestFilepondsModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        Schema::create('test_fileponds_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->model = new TestFilepondsModel([
            'name' => 'Test Fileponds Model',
        ]);
        $this->model->save();
    }

    public function test_model_uses_has_fileponds_trait()
    {
        $traits = class_uses_recursive($this->model);
        $this->assertContains('Unusualify\Modularity\Entities\Traits\HasFileponds', $traits);
    }

    public function test_fileponds_relationship_exists()
    {
        $this->assertTrue(method_exists($this->model, 'fileponds'));

        $relation = $this->model->fileponds();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $relation);
    }

    public function test_fileponds_relationship_configuration()
    {
        $relation = $this->model->fileponds();

        $this->assertEquals('filepondable_id', $relation->getForeignKeyName());
        $this->assertEquals('filepondable_type', $relation->getMorphType());
        $this->assertEquals(Filepond::class, $relation->getRelated()::class);
    }

    public function test_get_filepondable_class_returns_self_by_default()
    {
        $filepondableClass = $this->model->getFilepondableClass();

        $this->assertSame($this->model, $filepondableClass);
    }

    public function test_get_filepondable_class_returns_custom_class_when_set()
    {
        $this->model->filepondableClass = TestCustomFilepondsModel::class;

        $filepondableClass = $this->model->getFilepondableClass();

        $this->assertInstanceOf(TestCustomFilepondsModel::class, $filepondableClass);
        $this->assertEquals($this->model->getKey(), $filepondableClass->getKey());
        $this->assertEquals($this->model->name, $filepondableClass->name);
    }

    public function test_can_create_filepond()
    {
        $filepondData = [
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'test-uuid-123',
            'file_name' => 'test-file.pdf',
            'role' => 'document',
            'locale' => 'en',
        ];

        $filepond = new Filepond($filepondData);
        $this->model->fileponds()->save($filepond);

        $this->assertDatabaseHas('um_fileponds', [
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'test-uuid-123',
            'file_name' => 'test-file.pdf',
            'role' => 'document',
        ]);
    }

    public function test_can_retrieve_fileponds()
    {
        // Create multiple fileponds
        $filepond1 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'uuid-1',
            'file_name' => 'file1.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);

        $this->model->fileponds()->save($filepond1);

        $filepond2 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'uuid-2',
            'file_name' => 'file2.jpg',
            'role' => 'image',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond2);

        // Refresh model to load relationships
        $this->model->refresh();

        $fileponds = $this->model->fileponds;
        $this->assertCount(2, $fileponds);

        // Check first filepond
        $this->assertEquals($this->model->id, $fileponds->first()->filepondable_id);
        $this->assertEquals(get_class($this->model), $fileponds->first()->filepondable_type);
        $this->assertEquals('file1.pdf', $fileponds->first()->file_name);

        // Check second filepond
        $this->assertEquals('file2.jpg', $fileponds->get(1)->file_name);
    }

    public function test_get_fileponds_method_returns_collection()
    {
        // Create a filepond
        $filepond = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'test-uuid',
            'file_name' => 'test.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond);

        $fileponds = $this->model->getFileponds();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $fileponds);
        $this->assertCount(1, $fileponds);
        $this->assertEquals('test.pdf', $fileponds->first()->file_name);
    }

    public function test_has_filepond_returns_true_when_fileponds_exist()
    {
        // Create a filepond
        $filepond = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'test-uuid',
            'file_name' => 'test.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond);

        $this->assertTrue($this->model->hasFilepond());
    }

    public function test_has_filepond_returns_false_when_no_fileponds_exist()
    {
        $this->assertFalse($this->model->hasFilepond());
    }

    public function test_has_filepond_with_specific_role()
    {
        // Create fileponds with different roles
        $filepond1 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'uuid-1',
            'file_name' => 'document.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond1);

        $filepond2 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'uuid-2',
            'file_name' => 'image.jpg',
            'role' => 'image',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond2);

        // Test specific roles
        $this->assertTrue($this->model->hasFilepond('document'));
        $this->assertTrue($this->model->hasFilepond('image'));
        $this->assertFalse($this->model->hasFilepond('video'));
    }

    public function test_fileponds_can_have_different_locales()
    {
        $filepond = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'test-uuid',
            'file_name' => 'test.pdf',
            'role' => 'document',
            'locale' => 'tr',
        ]);
        $this->model->fileponds()->save($filepond);

        $savedFilepond = $this->model->fileponds()->first();

        $this->assertEquals('tr', $savedFilepond->locale);
    }

    public function test_multiple_models_can_have_different_fileponds()
    {
        // Create another model
        $model2 = new TestFilepondsModel(['name' => 'Second Model']);
        $model2->save();

        // Add fileponds to first model
        $filepond1 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'uuid-1',
            'file_name' => 'model1-file.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond1);

        // Add fileponds to second model
        $filepond2 = new Filepond([
            'filepondable_id' => $model2->id,
            'filepondable_type' => get_class($model2),
            'uuid' => 'uuid-2',
            'file_name' => 'model2-file.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $model2->fileponds()->save($filepond2);

        // Verify each model has its own fileponds
        $this->assertCount(1, $this->model->fileponds);
        $this->assertCount(1, $model2->fileponds);

        $this->assertEquals('model1-file.pdf', $this->model->fileponds->first()->file_name);
        $this->assertEquals('model2-file.pdf', $model2->fileponds->first()->file_name);
    }

    public function test_filepond_uuid_must_be_unique()
    {
        $filepond1 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'unique-uuid',
            'file_name' => 'file1.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);
        $this->model->fileponds()->save($filepond1);

        // Try to create another filepond with the same UUID
        $filepond2 = new Filepond([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'uuid' => 'unique-uuid',
            'file_name' => 'file2.pdf',
            'role' => 'document',
            'locale' => 'en',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->model->fileponds()->save($filepond2);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_fileponds_models');
        Schema::dropIfExists('um_fileponds');
        parent::tearDown();
    }
}

// Test model that uses the HasFileponds trait
class TestFilepondsModel extends Model
{
    use HasFileponds;

    protected $table = 'test_fileponds_models';
    protected $fillable = ['name'];
}

// Custom test model for filepondableClass testing
class TestCustomFilepondsModel extends Model
{
    use HasFileponds;

    protected $table = 'test_fileponds_models';
    protected $fillable = ['name'];
}
