<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Unusualify\Modularous\Entities\Traits\HasSpreadable;
use Unusualify\Modularous\Tests\TestCase;

class HasSpreadableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('spreadable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('spreadable_models');

        parent::tearDown();
    }

    public function test_clean_spreadable_attributes_removes_non_column_attributes(): void
    {
        $model = new class extends Model
        {
            use HasSpreadable;

            protected $table = 'spreadable_models';

            protected $guarded = [];
        };

        $model->forceFill([
            'name' => 'Example',
            'virtual_field' => 'remove-me',
        ]);

        $method = new ReflectionMethod($model, 'cleanSpreadableAttributes');
        $method->setAccessible(true);
        $method->invoke($model);

        $this->assertSame(['name' => 'Example'], $model->getAttributes());
        $this->assertArrayNotHasKey('virtual_field', $model->getAttributes());
    }
}
