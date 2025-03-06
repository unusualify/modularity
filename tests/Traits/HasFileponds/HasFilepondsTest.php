<?php

namespace Unusualify\Modularity\Tests\Traits\HasFileponds;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Collection;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Filepond;
use Illuminate\Database\Eloquent\Model;

class HasFilepondsTest extends TestCase
{
    use RefreshDatabase;

    protected $model;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_models', function ($table) {
            $table->id();
            $table->timestamps();
        });

        $this->model = new TestModel();
        $this->model->save();
        //dd($this->model->id);
        //$this->model->save();

    }

    /** @test */
    public function it_has_fileponds_relation()
    {

        $relation = $this->model->fileponds();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertEquals('filepondable_type', $relation->getMorphType());
        $this->assertEquals('filepondable_id', $relation->getForeignKeyName());
        $this->assertEquals(Filepond::class, $relation->getRelated()::class);

    }

    private function createFilepond($modelId, $modelClass, $role = null)
    {
        $filepond = new Filepond();
        $filepond->filepondable_id = $modelId;
        $filepond->filepondable_type = $modelClass;
        $filepond->role = $role;

        return $filepond;
    }
    public function it_can_get_fileponds()
    {
        //dd($this->model->id);
        // Create some test fileponds manually for our model
        $this->createFilepond($this->model->id, TestModel::class);
        //dd($this->model->id);
        $this->createFilepond($this->model->id, TestModel::class);
        $this->createFilepond($this->model->id, TestModel::class);


        $fileponds = $this->model->getFileponds();


        $this->assertInstanceOf(Collection::class, $fileponds);
        $this->assertCount(3, $fileponds);
        //dd('desfs');
        $this->assertInstanceOf(Filepond::class, $fileponds->first());
    }

    public function it_returns_empty_collection_when_no_fileponds_exist()
    {
        $fileponds = $this->model->getFileponds();

        $this->assertInstanceOf(Collection::class, $fileponds);
        $this->assertCount(0, $fileponds);
    }

    public function it_can_check_if_has_any_fileponds()
    {
        // Initially should have no fileponds
        $this->assertFalse($this->model->hasFilepond());

        // Create a filepond
        factory(Filepond::class)->create([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
        ]);

        // Now should have fileponds
        $this->assertTrue($this->model->hasFilepond());
    }

    public function it_can_check_if_has_fileponds_with_specific_role()
    {
        // Create fileponds with different roles
        factory(Filepond::class)->create([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'role' => 'avatar',
        ]);

        factory(Filepond::class)->create([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'role' => 'document',
        ]);

        // Test role-specific checks
        $this->assertTrue($this->model->hasFilepond('avatar'));
        $this->assertTrue($this->model->hasFilepond('document'));
        $this->assertFalse($this->model->hasFilepond('background'));

        // Should still return true for any filepond
        $this->assertTrue($this->model->hasFilepond());
    }

    public function it_returns_false_for_nonexistent_role()
    {
        // Create a filepond with a specific role
        factory(Filepond::class)->create([
            'filepondable_id' => $this->model->id,
            'filepondable_type' => get_class($this->model),
            'role' => 'avatar',
        ]);

        // Check for a different role
        $this->assertFalse($this->model->hasFilepond('document'));
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }
}

class TestModel extends Model{

    use HasFileponds;
}
