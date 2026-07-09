<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Jobs\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Contracts\Cache\CacheableInterface;
use Unusualify\Modularous\Contracts\ModuleableInterface;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\InvalidateModelCacheJob;
use Unusualify\Modularous\Tests\TestCase;

class InvalidateModelCacheJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('invalidate_model_cache_models');
        Schema::create('invalidate_model_cache_models', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.queue.name', 'modularous-cache-test');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.formItem', true);
        Config::set('modularous.cache.observer.auto_invalidate', true);

        $this->app->forgetInstance('modularous.cache');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('invalidate_model_cache_models');

        parent::tearDown();
    }

    /** @test */
    public function it_always_invalidates_relation_tags_before_route_logic(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Tagged']);

        ModularousCache::shouldReceive('invalidateByRelatedModel')
            ->once()
            ->with(InvalidateModelCacheTestModel::class, $model->getKey());
        ModularousCache::shouldReceive('isEnabled')
            ->with('TestModule', 'Item')
            ->andReturn(false);
        ModularousCache::shouldReceive('invalidateForModel')->never();
        ModularousCache::shouldReceive('refreshModelCaches')->never();

        (new InvalidateModelCacheJob($model, 'updated'))->handle();
    }

    /** @test */
    public function it_refreshes_presentation_only_caches_without_tag_invalidation(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Refresh']);

        ModularousCache::shouldReceive('invalidateByRelatedModel')->once();
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->withArgs(function (Model $passedModel, array $types, array $options) use ($model) {
                return $passedModel->getKey() === $model->getKey()
                    && ($types['presentationItem'] ?? false) === true
                    && count($types) === 1
                    && $options['warmup'] === true
                    && $options['moduleName'] === 'TestModule'
                    && $options['moduleRouteName'] === 'Item';
            });
        ModularousCache::shouldReceive('invalidateForModel')->never();

        (new InvalidateModelCacheJob($model, 'updated', [
            'presentationItem' => true,
            'counts' => false,
            'index' => false,
            'record' => false,
            'formItem' => false,
            'formattedItem' => false,
        ]))->handle();
    }

    /** @test */
    public function it_invalidates_multiple_types_for_model_updates(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Multi']);

        ModularousCache::shouldReceive('invalidateByRelatedModel')->once();
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
        ModularousCache::shouldReceive('invalidateForModel')
            ->once()
            ->withArgs(function (Model $passedModel, array $types, array $options) use ($model) {
                return $passedModel->getKey() === $model->getKey()
                    && ($types['formItem'] ?? false) === true
                    && ($types['presentationItem'] ?? false) === true
                    && ($options['skipInvalidation'] ?? false) === false
                    && ($options['warmup'] ?? false) === true;
            });
        ModularousCache::shouldReceive('refreshModelCaches')->never();

        (new InvalidateModelCacheJob($model, 'updated', [
            'formItem' => true,
            'presentationItem' => true,
        ]))->handle();
    }

    /** @test */
    public function it_purges_all_enabled_types_for_deleted_events_without_warmup(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Deleted']);
        $model->delete();

        ModularousCache::shouldReceive('invalidateByRelatedModel')->once();
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('isEnabled')
            ->with('TestModule', 'Item', \Mockery::type('string'))
            ->andReturn(true);
        ModularousCache::shouldReceive('invalidateForModel')
            ->once()
            ->withArgs(function (Model $passedModel, array $types, array $options) use ($model) {
                return $passedModel->getKey() === $model->getKey()
                    && ($types['presentationItem'] ?? false) === true
                    && ($types['formItem'] ?? false) === true
                    && ($options['warmup'] ?? true) === false;
            });
        ModularousCache::shouldReceive('refreshModelCaches')->never();

        (new InvalidateModelCacheJob($model, 'deleted'))->handle();
    }

    /** @test */
    public function it_skips_warmup_for_created_events(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Created']);

        ModularousCache::shouldReceive('invalidateByRelatedModel')->once();
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
        ModularousCache::shouldReceive('invalidateForModel')
            ->once()
            ->withArgs(function ($passedModel, $types, array $options) use ($model) {
                return $passedModel->getKey() === $model->getKey() && ($options['warmup'] ?? true) === false;
            });

        (new InvalidateModelCacheJob($model, 'created', ['formItem' => true]))->handle();
    }

    /** @test */
    public function it_filters_types_that_are_not_auto_invalidated(): void
    {
        $model = InvalidateModelCacheTestModel::query()->create(['name' => 'Manual']);

        ModularousCache::shouldReceive('invalidateByRelatedModel')->once();
        ModularousCache::shouldReceive('isEnabled')->andReturn(true);
        ModularousCache::shouldReceive('shouldAutoInvalidate')
            ->with('TestModule', 'Item', 'formItem')
            ->andReturn(false);
        ModularousCache::shouldReceive('shouldAutoInvalidate')
            ->with('TestModule', 'Item', 'presentationItem')
            ->andReturn(true);
        ModularousCache::shouldReceive('invalidateForModel')->never();
        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->withArgs(function ($passedModel, array $types) use ($model) {
                return $passedModel->getKey() === $model->getKey()
                    && ! ($types['formItem'] ?? false)
                    && ($types['presentationItem'] ?? false) === true;
            });

        (new InvalidateModelCacheJob($model, 'updated', [
            'formItem' => true,
            'presentationItem' => true,
        ]))->handle();
    }
}

class InvalidateModelCacheTestModel extends Model implements CacheableInterface, ModuleableInterface
{
    protected $table = 'invalidate_model_cache_models';

    protected $fillable = ['name'];

    private bool $cacheEnabled = true;

    private ?string $moduleName = 'TestModule';

    private ?string $routeName = 'Item';

    public function shouldUseCache(?string $type = null): bool
    {
        return $this->cacheEnabled;
    }

    public function withCache(bool $enabled = true): static
    {
        $this->cacheEnabled = $enabled;

        return $this;
    }

    public function withoutCache(): static
    {
        return $this->withCache(false);
    }

    public function getCacheModuleName(): ?string
    {
        return $this->moduleName;
    }

    public function getCacheModuleRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setModuleName(string $moduleName): static
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function setRouteName(string $routeName): static
    {
        $this->routeName = $routeName;

        return $this;
    }
}
