<?php

namespace Unusualify\Modularity\Tests\Traits\HasPosition;

use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class HasPositionTest extends TestCase
{
    use RefreshDatabase;

    protected function setup(): void{
        parent::setup();
        Schema::create('position_models', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    protected function createModel()
    {
        return PositionModel::create();
    }

    /** @test */
    public function it_sets_position_to_last_position_on_creation()
    {
        $model1 = $this->createModel();
        $model2 = $this->createModel();
        $this->assertEquals(1, $model1->position);
        $this->assertEquals(2, $model2->position);

    }

    /** @test */
    public function it_retrieves_the_correct_last_position()
    {
        $this->createModel();
        $this->createModel();

        //FaqHasPosition::setNewOrder([$model6->id, $model5->id, $model4->id], 4);

        //$this->assertGreaterThan(0, -2, 'startOrder must be a positive number.');
        //assertFalse(FaqHasPosition::setNewOrder([$model6->id, $model5->id],-2));

        //dd(
        //    FaqHasPosition::ordered()->get()->map(fn($f) => $f->id),
        //    FaqHasPosition::orderBy($model1->getTable() . ".position")->get()->map(fn($f) => $f->id)
        //);
        // dd(
        //     FaqHasPosition::find(1)->position,
        //     FaqHasPosition::find(2)->position,
        //     FaqHasPosition::find(3)->position,
        //     FaqHasPosition::find(4)->position,
        //     FaqHasPosition::find(5)->position,
        //     FaqHasPosition::find(6)->position,
        //     FaqHasPosition::find(7)->position,
        //     FaqHasPosition::find(8)->position,
        // );

        $this->assertEquals(1, PositionModel::find(1)->position);
        $this->assertEquals(2, PositionModel::find(2)->position);
        //$this->assertEquals([1,2,5,4,3,6,7,8], FaqHasPosition::ordered()->get()->map(fn($f) => $f->id)->toArray());
        // $this->assertEquals(2, $model2->getCurrentLastPosition());
    }

    public function it_checks_positive_integer_for_start_order()
    {

        $model5 = $this->createModel();
        $model6 = $this->createModel();

        $this->expectExceptionMessage('$startOrder must be bigger or equal to 1');
        PositionModel::setNewOrder([$model6->id, $model5->id], -2);

    }

    /** @test */
    public function it_orders_models_by_position()
    {
        $model1 = $this->createModel(['position' => 3]);
        $model2 = $this->createModel(['position' => 1]);
        $model3 = $this->createModel(['position' => 2]);

        $orderedModels = PositionModel::ordered()->get();

        $this->assertEquals([1, 2, 3], $orderedModels->pluck('position')->toArray());
    }

    /** @test */

    public function it_sets_new_order_correctly()
    {
        $model1 = $this->createModel();
        $model2 = $this->createModel();
        $model3 = $this->createModel();

        PositionModel::setNewOrder([$model3->id, $model1->id, $model2->id]);

        $this->assertEquals(1, $model3->fresh()->position);

        $this->assertEquals(2, $model1->fresh()->position);
        $this->assertEquals(3, $model2->fresh()->position);
    }
}
