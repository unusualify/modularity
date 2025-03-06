<?php

namespace Unusualify\Modularity\Tests\Traits\HasRepeaters;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Entities\Repeater;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Entities\Traits\HasRepeaters;
use Unusualify\Modularity\Entities\Model;


class HasRepeatersTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp() : void
    {

        parent::setUp();

        Schema::create('test_models', function (Blueprint $table)
        {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        TestModel::create([
            'name' => 'Test Module',
        ]);


    }

        /** @test */
        public function it_has_repeaters_relationship()
        {
            $model = TestModel::find(1);

            $model->repeaters()->create([
                'content'         => ['key' => 'value'],
                'role'            => 'test_role',
                'locale'          => 'en',
            ]);

            $repeaters = Repeater::find(1);

            // 3. Modülün repeaters ilişkisini çağır ve repeater'ı içerdiğini doğrula
            $this->assertTrue($model->repeaters->contains($repeaters));
            $this->assertCount(1, $model->repeaters);
        }

        /** @test */
        public function it_returns_empty_repeaters_when_no_repeaters_exist()
        {
            $model = TestModel::find(1);

            $this->assertCount(0, $model->repeaters);
        }

}

class TestModel extends Model
{
    use HasRepeaters;
    protected $fillable = ['name'];

}




