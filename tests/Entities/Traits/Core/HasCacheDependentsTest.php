<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits\Core;

use Unusualify\Modularous\Entities\Traits\Core\HasCacheDependents;
use Unusualify\Modularous\Tests\TestCase;

class HasCacheDependentsTest extends TestCase
{
    /** @test */
    public function it_resolves_manual_config_and_dynamic_dependents(): void
    {
        config([
            'modularous.cache.dependencies' => [
                CacheDependentStub::class => ['config_module'],
            ],
        ]);

        $model = new CacheDependentStub;

        $this->assertContains('press_release', $model->getManualDependents());
        $this->assertContains('config_module', $model->getManualDependents());
        $this->assertContains('press_release', $model->getCacheDependents());
        $this->assertTrue($model->hasCacheDependents());
        $this->assertIsArray($model->getGraphDiscoveredDependents());

        $model->addCacheDependent('invoice');
        $this->assertContains('invoice', $model->getManualDependents());
    }
}

class CacheDependentStub
{
    use HasCacheDependents;

    protected array $cacheDependents = ['press_release'];
}
