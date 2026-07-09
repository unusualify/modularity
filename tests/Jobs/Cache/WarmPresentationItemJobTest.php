<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Tests\TestCase;

class WarmPresentationItemJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('warm_presentation_item_models');
        Schema::create('warm_presentation_item_models', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.queue.name', 'modularous-cache-test');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('warm_presentation_item_models');

        parent::tearDown();
    }

    /** @test */
    public function it_configures_the_modularous_cache_queue(): void
    {
        $model = new WarmPresentationItemTestModel(['name' => 'Example']);
        $model->id = 1;
        $model->exists = true;

        $job = new WarmPresentationItemJob($model, 'Blog', 'BlogLanding', 'en');

        $this->assertSame('modularous-cache-test', $job->queue);
        $this->assertSame('Blog', $job->moduleName);
        $this->assertSame('BlogLanding', $job->moduleRouteName);
        $this->assertSame('en', $job->locale);
    }

    /** @test */
    public function it_refreshes_presentation_item_caches_for_the_model(): void
    {
        $model = WarmPresentationItemTestModel::query()->create(['name' => 'Cached']);

        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->withArgs(function (Model $passedModel, array $types, array $options) use ($model) {
                return $passedModel->is($model)
                    && ($types['presentationItem'] ?? false) === true
                    && ($types['counts'] ?? true) === false
                    && $options['moduleName'] === 'Blog'
                    && $options['moduleRouteName'] === 'BlogLanding'
                    && $options['locale'] === 'tr';
            });

        (new WarmPresentationItemJob($model, 'Blog', 'BlogLanding', 'tr'))->handle();
    }

    /** @test */
    public function it_reloads_the_model_when_the_serialized_instance_is_not_persisted(): void
    {
        $model = WarmPresentationItemTestModel::query()->create(['name' => 'Reload me']);
        $stale = new WarmPresentationItemTestModel(['name' => 'stale']);
        $stale->id = $model->getKey();
        $stale->exists = false;

        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->withArgs(function (Model $passedModel) use ($model) {
                return $passedModel->exists && $passedModel->is($model);
            });

        (new WarmPresentationItemJob($stale, 'Blog', 'BlogLanding'))->handle();
    }
}

class WarmPresentationItemTestModel extends Model
{
    protected $table = 'warm_presentation_item_models';

    protected $fillable = ['name'];
}
