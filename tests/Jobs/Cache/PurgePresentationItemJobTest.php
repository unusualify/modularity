<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\PurgePresentationItemJob;
use Unusualify\Modularous\Tests\TestCase;

class PurgePresentationItemJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('purge_presentation_item_models');
        Schema::create('purge_presentation_item_models', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.queue.name', 'modularous-cache-test');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('purge_presentation_item_models');

        parent::tearDown();
    }

    /** @test */
    public function it_configures_the_modularous_cache_queue(): void
    {
        $model = new PurgePresentationItemTestModel(['name' => 'Example']);
        $model->id = 1;
        $model->exists = true;

        $job = new PurgePresentationItemJob($model, 'Blog', 'BlogLanding', 'en');

        $this->assertSame('modularous-cache-test', $job->queue);
    }

    /** @test */
    public function it_purges_presentation_item_files_for_the_model(): void
    {
        $model = PurgePresentationItemTestModel::query()->create(['name' => 'Purge me']);

        ModularousCache::shouldReceive('purgePresentationItemForModel')
            ->once()
            ->with($model, 'Blog', 'BlogLanding', 'en')
            ->andReturn(3);

        (new PurgePresentationItemJob($model, 'Blog', 'BlogLanding', 'en'))->handle();
    }

    /** @test */
    public function it_reloads_the_model_when_the_serialized_instance_is_not_persisted(): void
    {
        $model = PurgePresentationItemTestModel::query()->create(['name' => 'Reload me']);
        $stale = new PurgePresentationItemTestModel(['name' => 'stale']);
        $stale->id = $model->getKey();
        $stale->exists = false;

        ModularousCache::shouldReceive('purgePresentationItemForModel')
            ->once()
            ->withArgs(function (Model $passedModel) use ($model) {
                return $passedModel->exists && $passedModel->is($model);
            })
            ->andReturn(1);

        (new PurgePresentationItemJob($stale, 'Blog', 'BlogLanding'))->handle();
    }
}

class PurgePresentationItemTestModel extends Model
{
    protected $table = 'purge_presentation_item_models';

    protected $fillable = ['name'];
}
