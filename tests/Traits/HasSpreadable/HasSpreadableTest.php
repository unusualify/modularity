<?php

namespace Unusualify\Modularity\Tests\Traits\HasSpreadable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Spread;
use Unusualify\Modularity\Entities\Traits\HasSpreadable;

class SpreadModel extends Model
{
    use HasSpreadable;

    // Allow mass assignment of the database column and the _spread attribute.
    protected $fillable = ['title'];
}
class HasSpreadableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the table for our test model.
        Schema::create('spread_models', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Helper method to create a SpreadModel.
     */
    protected function createModel(array $attributes = [])
    {
        return SpreadModel::create($attributes);
    }

    /** @test */
    public function it_creates_a_spread_record_on_creation()
    {
        // dd(Spread::all());
        $data = ['foo' => 'bar'];
        // Set the _spread attribute so it is used as the pending spread data.
        $model = $this->createModel(['_spread' => $data]);

        $this->assertNotNull($model->spreadable, 'Spread record should have been created.');
        $this->assertEquals($data, $model->spreadable->content);
    }

    /** @test */
    public function it_updates_spread_record_on_model_update()
    {
        $initialData = ['foo' => 'bar'];
        $model = $this->createModel(['_spread' => $initialData]);

        $this->assertEquals($initialData, $model->spreadable->content);

        // Update the _spread property with new data.
        $newData = ['foo' => 'updated', 'baz' => 'qux'];
        $model->_spread = $newData;
        $model->save();

        // Ensure that the spread record was updated.
        $this->assertEquals($newData, $model->fresh()->spreadable->content);

        // Verify that on retrieval the new spread data is merged into the model.
        $freshModel = SpreadModel::find($model->id);
        $this->assertEquals('updated', $freshModel->foo);
        $this->assertEquals('qux', $freshModel->baz);
        $this->assertEquals($newData, $freshModel->_spread);
    }

    /** @test */
    public function it_initializes_empty_spread_when_no_spreadable_exists_on_retrieval()
    {
        $model = $this->createModel(['_spread' => ['foo' => 'bar']]);
        // Manually delete the related spread record.
        $model->spreadable()->delete();

        $freshModel = SpreadModel::find($model->id);
        $this->assertEmpty($freshModel->_spread, 'When no spread record exists, _spread should be an empty array.');
    }

    /** @test */
    public function it_does_not_override_protected_attributes_on_retrieval()
    {
        // Here "title" is a database column (and thus protected).
        // The spread data also contains a "title" key along with a non-reserved "extra" key.
        $model = $this->createModel([
            'title'   => 'Original Title',
            '_spread' => ['title' => 'Spread Title', 'extra' => 'value'],
        ]);

        $freshModel = SpreadModel::find($model->id);

        // The database column "title" must remain unchanged.
        $this->assertEquals('Original Title', $freshModel->title);
        // Non-protected attribute from the spread should be merged.
        $this->assertEquals('value', $freshModel->extra);
        // The _spread attribute still contains the full spread data.
        $this->assertEquals(['title' => 'Spread Title', 'extra' => 'value'], $freshModel->_spread);
    }

    /** @test */
    public function it_does_not_update_spread_record_if_no_new_spread_data_on_model_update()
    {
        $data = ['foo' => 'bar'];
        $model = $this->createModel(['_spread' => $data]);

        $originalContent = $model->spreadable->content;

        // Update a different attribute without modifying _spread.
        $model->title = 'New Title';
        $model->save();

        // The spread content should remain unchanged.
        $this->assertEquals($originalContent, $model->fresh()->spreadable->content);
    }

    /** @test */
    public function it_initializes_empty_spread_when_no_spread_data_is_provided_on_creation()
    {
        // Create a model without providing _spread.
        $model = $this->createModel(['title' => 'Test Title']);

        $freshModel = SpreadModel::find($model->id);
        // Even if no _spread data was provided, the _spread attribute should be set to an empty array.
        $this->assertEquals([], $freshModel->_spread);
    }

    /** @test */
    public function it_handles_multiple_updates_to_spread_data_correctly()
    {
        $data1 = ['foo' => 'bar'];
        $model = $this->createModel(['_spread' => $data1]);
        $this->assertEquals($data1, $model->spreadable->content);

        $data2 = ['foo' => 'updated', 'baz' => 'qux'];
        $model->_spread = $data2;
        $model->save();
        $this->assertEquals($data2, $model->fresh()->spreadable->content);

        $data3 = ['new' => 'data'];
        $model->_spread = $data3;
        $model->save();
        $this->assertEquals($data3, $model->fresh()->spreadable->content);
    }

    /** @test */
    public function it_returns_a_morph_one_relation_for_spreadable()
    {
        $model = $this->createModel(['_spread' => ['foo' => 'bar']]);
        $relation = $model->spreadable();

        $this->assertInstanceOf(MorphOne::class, $relation);
    }
}
