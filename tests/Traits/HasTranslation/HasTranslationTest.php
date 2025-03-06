<?php

namespace Unusualify\Modularity\Tests\Traits\HasTranslation;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Tests\Traits\HasTranslation\Models\Package;
use Unusualify\Modularity\Tests\Traits\HasTranslation\Models\PackageContinent;


class HasTranslationTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function model_with_translation_test()
    {
        $packageModel = new Package();
        $packageModel->getTranslationModelNameDefault();
    }

    /** @test */
    public function model_without_translation_test()
    {
        $packageModel = new PackageContinent();
        $packageModel->getTranslationModelNameDefault();
    }

}

