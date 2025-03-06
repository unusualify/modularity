<?php

namespace Unusualify\Modularity\Tests\Traits;

use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\Traits\IsTranslatable\OwnToHasTranslation;
use Unusualify\Modularity\Tests\Traits\IsTranslatable\NotOwnToHasTranslation;
use Unusualify\Modularity\Tests\Traits\IsTranslatable\OwnToHasTranslationWithoutTranslatedAtt;
use Unusualify\Modularity\Tests\Traits\IsTranslatable\OwnToHasTranslationEmptyTranslatedAtt;

class IsTranslatableTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function it_returns_true_when_model_is_translatable()
    {

        $model1 = new OwnToHasTranslation();
        $model2 = new NotOwnToHasTranslation();

        $this->assertTrue($model1->isTranslatable());
        $this->assertFalse($model2->isTranslatable());

    }

    /** @test */
    public function it_returns_false_when_translated_attributes_property_is_missing()
    {

        $model = new OwnToHasTranslationWithoutTranslatedAtt();

        $this->assertFalse($model->isTranslatable());
    }

    /** @test */
    public function it_returns_false_when_translated_attributes_is_empty()
    {

        $model = new OwnToHasTranslationEmptyTranslatedAtt();

        $this->assertFalse($model->isTranslatable());
    }

    /** @test */
    public function it_returns_true_when_specified_columns_are_translatable()
    {
        $model = new OwnToHasTranslation();

        $this->assertTrue($model->isTranslatable(['title']));
    }

    /** @test */
    public function it_returns_false_when_specified_columns_are_not_translatable()
    {
        $model = new OwnToHasTranslation();

        $this->assertFalse($model->isTranslatable(['non_existent_column']));
    }

}

