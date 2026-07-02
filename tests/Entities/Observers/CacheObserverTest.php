<?php

namespace Unusualify\Modularous\Tests\Entities\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Entities\Observers\CacheObserver;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\Cache\DependentCacheInvalidator;
use Unusualify\Modularous\Tests\TestCase;

class CacheObserverTest extends TestCase
{
    protected TestableCacheObserver $observer;

    protected TestableDependentCacheInvalidator $dependentInvalidator;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.enabled', false);
        Config::set('modularous.cache.observer.queue', false);
        RelationshipSourceModelWithoutMetadata::$findResult = null;
        RelationshipSourceModelWithMetadata::$findResult = null;
        PackageRegionStub::$findResult = null;

        $this->dependentInvalidator = new TestableDependentCacheInvalidator;
        $this->app->instance(DependentCacheInvalidator::class, $this->dependentInvalidator);
        $this->observer = new TestableCacheObserver;
    }

    protected function mockAutoInvalidationEnabled(): void
    {
        ModularousCache::shouldReceive('shouldAutoInvalidate')->andReturn(true);
    }

    public function test_created_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->created($model);

        $this->assertTrue(true);
    }

    public function test_updated_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->updated($model);

        $this->assertTrue(true);
    }

    public function test_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->deleted($model);

        $this->assertTrue(true);
    }

    public function test_restored_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->restored($model);

        $this->assertTrue(true);
    }

    public function test_force_deleted_does_not_throw_when_cache_disabled()
    {
        $model = $this->createTestModel();

        $this->observer->forceDeleted($model);

        $this->assertTrue(true);
    }

    public function test_config_dependents_run_when_tag_invalidation_succeeds(): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.dependencies', [
            SourceCacheDependentModel::class => [
                [
                    'moduleName' => 'DependentModule',
                    'moduleRouteName' => 'DependentRoute',
                    'types' => [
                        'formItem' => true,
                        'formattedItem' => true,
                    ],
                ],
            ],
        ]);

        $model = new SourceCacheDependentModel;
        $model->setRawAttributes(['id' => 5]);
        $model->exists = true;

        $mockModule = $this->mockDependentModule();

        Modularous::shouldReceive('hasModule')->with('DependentModule')->andReturn(true);
        Modularous::shouldReceive('find')->with('DependentModule')->andReturn($mockModule);

        ModularousCache::shouldReceive('isEnabled')
            ->with('DependentModule', 'DependentRoute')
            ->andReturn(true);

        $this->mockAutoInvalidationEnabled();

        $this->observer->exposeInvalidateDependentModules($model);

        $this->assertCount(1, $this->dependentInvalidator->invalidateAllItemCachesCalls);
        $this->assertSame('DependentModule', $this->dependentInvalidator->invalidateAllItemCachesCalls[0]['moduleName']);
        $this->assertSame('DependentRoute', $this->dependentInvalidator->invalidateAllItemCachesCalls[0]['moduleRouteName']);
    }

    public function test_get_config_dependents_preserves_presentation_item_type(): void
    {
        Config::set('modularous.cache.dependencies', [
            SourceCacheDependentModel::class => [
                [
                    'moduleName' => 'DependentModule',
                    'moduleRouteName' => 'DependentRoute',
                    'types' => [
                        'presentationItem' => true,
                        'counts' => false,
                    ],
                ],
            ],
        ]);

        $mockModule = $this->mockDependentModule();

        Modularous::shouldReceive('hasModule')->with('DependentModule')->andReturn(true);
        Modularous::shouldReceive('find')->with('DependentModule')->andReturn($mockModule);

        $this->mockAutoInvalidationEnabled();

        $dependents = $this->dependentInvalidator->exposeGetConfigDependents(SourceCacheDependentModel::class);

        $this->assertCount(1, $dependents);
        $this->assertTrue($dependents[0]['types']['presentationItem']);
        $this->assertFalse($dependents[0]['types']['counts']);
        $this->assertTrue($dependents[0]['types']['formItem']);
    }

    public function test_dependent_with_should_warm_false_skips_warmup(): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.dependencies', [
            SourceCacheDependentModel::class => [
                [
                    'moduleName' => 'DependentModule',
                    'moduleRouteName' => 'DependentRoute',
                    'types' => [
                        'presentationItem' => true,
                    ],
                    'shouldWarm' => false,
                ],
            ],
        ]);

        $model = new SourceCacheDependentModel;
        $model->setRawAttributes(['id' => 5]);
        $model->exists = true;

        $mockModule = $this->mockDependentModule();

        Modularous::shouldReceive('hasModule')->with('DependentModule')->andReturn(true);
        Modularous::shouldReceive('find')->with('DependentModule')->andReturn($mockModule);

        ModularousCache::shouldReceive('isEnabled')
            ->with('DependentModule', 'DependentRoute')
            ->andReturn(true);

        $this->mockAutoInvalidationEnabled();

        $this->observer->exposeInvalidateDependentModules($model);

        $this->assertCount(1, $this->dependentInvalidator->invalidateAllItemCachesCalls);
        $this->assertFalse($this->dependentInvalidator->invalidateAllItemCachesCalls[0]['shouldWarmDependentModules']);
    }

    public function test_package_region_update_warms_all_package_country_presentation_items_when_tags_invalidate_succeeds(): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.dependencies', [
            PackageRegionStub::class => [
                [
                    'moduleName' => 'BusinessPackage',
                    'moduleRouteName' => 'PackageCountry',
                    'types' => [
                        'counts' => false,
                        'index' => false,
                        'record' => false,
                        'formattedItem' => false,
                        'formItem' => false,
                        'presentationItem' => true,
                    ],
                    'targetRelationshipName' => 'packageCountries',
                    'shouldWarm' => true,
                ],
            ],
        ]);

        $region = new PackageRegionStub;
        $region->setRawAttributes(['id' => 9]);
        $region->exists = true;

        $firstCountry = new PackageCountryStub;
        $firstCountry->setRawAttributes(['id' => 201]);
        $firstCountry->exists = true;

        $secondCountry = new PackageCountryStub;
        $secondCountry->setRawAttributes(['id' => 202]);
        $secondCountry->exists = true;

        $region->setRelation('packageCountries', collect([$firstCountry, $secondCountry]));
        PackageRegionStub::$findResult = $region;

        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('PackageCountry')->andReturn(true);
        $mockModule->shouldReceive('isEnabledRoute')->with('PackageCountry')->andReturn(true);
        $mockModule->shouldReceive('getModel')->with('PackageCountry')->andReturn(new PackageCountryStub);

        Modularous::shouldReceive('hasModule')->with('BusinessPackage')->andReturn(true);
        Modularous::shouldReceive('find')->with('BusinessPackage')->andReturn($mockModule);

        ModularousCache::shouldReceive('isEnabled')
            ->with('BusinessPackage', 'PackageCountry')
            ->andReturn(true);

        $this->mockAutoInvalidationEnabled();

        ModularousCache::shouldReceive('invalidateForModel')->never();
        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->with($firstCountry, \Mockery::on(function (array $types): bool {
                return $types['presentationItem'] === true
                    && $types['formItem'] === false
                    && $types['formattedItem'] === false;
            }), [
                'moduleName' => 'BusinessPackage',
                'moduleRouteName' => 'PackageCountry',
            ]);

        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->with($secondCountry, \Mockery::type('array'), [
                'moduleName' => 'BusinessPackage',
                'moduleRouteName' => 'PackageCountry',
            ]);

        $this->observer->exposeInvalidateDependentModules($region);

        $this->assertEmpty($this->dependentInvalidator->invalidateAllItemCachesCalls);
    }

    public function test_target_relationship_strict_gate_skips_without_get_eloquent_relationships(): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.dependencies', [
            RelationshipSourceModelWithoutMetadata::class => [
                [
                    'moduleName' => 'DependentModule',
                    'moduleRouteName' => 'DependentRoute',
                    'types' => [
                        'presentationItem' => true,
                    ],
                    'targetRelationshipName' => 'dependentItems',
                    'shouldWarm' => true,
                ],
            ],
        ]);

        $source = new RelationshipSourceModelWithoutMetadata;
        $source->setRawAttributes(['id' => 3]);
        $source->exists = true;

        $firstTarget = new DependentRouteModel;
        $firstTarget->setRawAttributes(['id' => 101]);
        $firstTarget->exists = true;

        $source->setRelation('dependentItems', collect([$firstTarget]));
        RelationshipSourceModelWithoutMetadata::$findResult = $source;

        $mockModule = $this->mockDependentModule();

        Modularous::shouldReceive('hasModule')->with('DependentModule')->andReturn(true);
        Modularous::shouldReceive('find')->with('DependentModule')->andReturn($mockModule);

        ModularousCache::shouldReceive('isEnabled')
            ->with('DependentModule', 'DependentRoute')
            ->andReturn(true);

        $this->mockAutoInvalidationEnabled();

        ModularousCache::shouldReceive('invalidateForModel')->never();
        ModularousCache::shouldReceive('warmupModelCaches')->never();
        ModularousCache::shouldReceive('refreshModelCaches')->never();

        $this->observer->exposeInvalidateDependentModules($source);

        $this->assertEmpty($this->dependentInvalidator->invalidateAllItemCachesCalls);
    }

    public function test_target_relationship_with_metadata_invalidates_once_and_warms_each_related_item(): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.dependencies', [
            RelationshipSourceModelWithMetadata::class => [
                [
                    'moduleName' => 'DependentModule',
                    'moduleRouteName' => 'DependentRoute',
                    'types' => [
                        'presentationItem' => true,
                        'formItem' => false,
                        'formattedItem' => false,
                        'record' => false,
                        'counts' => false,
                        'index' => false,
                    ],
                    'targetRelationshipName' => 'dependentItems',
                    'shouldWarm' => true,
                ],
            ],
        ]);

        $source = new RelationshipSourceModelWithMetadata;
        $source->setRawAttributes(['id' => 3]);
        $source->exists = true;

        $firstTarget = new DependentRouteModel;
        $firstTarget->setRawAttributes(['id' => 101]);
        $firstTarget->exists = true;

        $secondTarget = new DependentRouteModel;
        $secondTarget->setRawAttributes(['id' => 102]);
        $secondTarget->exists = true;

        $source->setRelation('dependentItems', collect([$firstTarget, $secondTarget]));
        RelationshipSourceModelWithMetadata::$findResult = $source;

        $mockModule = $this->mockDependentModule();

        Modularous::shouldReceive('hasModule')->with('DependentModule')->andReturn(true);
        Modularous::shouldReceive('find')->with('DependentModule')->andReturn($mockModule);

        ModularousCache::shouldReceive('isEnabled')
            ->with('DependentModule', 'DependentRoute')
            ->andReturn(true);

        $this->mockAutoInvalidationEnabled();

        ModularousCache::shouldReceive('invalidateForModel')->never();
        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->with($firstTarget, \Mockery::type('array'), [
                'moduleName' => 'DependentModule',
                'moduleRouteName' => 'DependentRoute',
            ]);

        ModularousCache::shouldReceive('refreshModelCaches')
            ->once()
            ->with($secondTarget, \Mockery::type('array'), [
                'moduleName' => 'DependentModule',
                'moduleRouteName' => 'DependentRoute',
            ]);

        $this->observer->exposeInvalidateDependentModules($source);

        $this->assertEmpty($this->dependentInvalidator->invalidateAllItemCachesCalls);
    }

    private function mockDependentModule(): Module
    {
        $mockModule = \Mockery::mock(Module::class);
        $mockModule->shouldReceive('hasRoute')->with('DependentRoute')->andReturn(true);
        $mockModule->shouldReceive('isEnabledRoute')->with('DependentRoute')->andReturn(true);
        $mockModule->shouldReceive('getModel')->with('DependentRoute')->andReturn(new DependentRouteModel);

        return $mockModule;
    }

    private function createTestModel(): Model
    {
        $model = new class extends Model
        {
            protected $table = 'test_models';

            public function getKey()
            {
                return 1;
            }
        };
        $model->setRawAttributes(['id' => 1]);

        return $model;
    }
}

class TestableCacheObserver extends CacheObserver
{
    public function exposeInvalidateDependentModules(Model $model): void
    {
        app(DependentCacheInvalidator::class)->invalidateForModel($model);
    }
}

class TestableDependentCacheInvalidator extends DependentCacheInvalidator
{
    public array $invalidateAllItemCachesCalls = [];

    public function exposeGetConfigDependents(string $modelClass): array
    {
        return $this->getConfigDependentsForModelClass($modelClass);
    }

    protected function invalidateAllItemCaches(
        string $moduleName,
        string $moduleRouteName,
        array $types,
        bool $shouldWarmDependentModules = true,
    ): void {
        $this->invalidateAllItemCachesCalls[] = [
            'moduleName' => $moduleName,
            'moduleRouteName' => $moduleRouteName,
            'types' => $types,
            'shouldWarmDependentModules' => $shouldWarmDependentModules,
        ];
    }
}

class SourceCacheDependentModel extends Model
{
    protected $table = 'source_models';
}

class DependentRouteModel extends Model
{
    protected $table = 'dependent_models';

    public $timestamps = false;
}

class PackageRegionStub extends Model
{
    protected $table = 'package_regions';

    public $timestamps = false;

    public static ?self $findResult = null;

    public static function find($id, $columns = ['*'])
    {
        return static::$findResult;
    }

    public function getEloquentRelationships(): array
    {
        return [
            'packageCountries' => [
                'relationship_class' => PackageCountryStub::class,
            ],
        ];
    }

    public function packageCountries(): HasMany
    {
        return $this->hasMany(PackageCountryStub::class);
    }
}

class PackageCountryStub extends Model
{
    protected $table = 'package_countries';

    public $timestamps = false;
}

class RelationshipSourceModelWithoutMetadata extends Model
{
    protected $table = 'relationship_source_models';

    public $timestamps = false;

    public static ?self $findResult = null;

    public static function find($id, $columns = ['*'])
    {
        return static::$findResult;
    }

    public function dependentItems(): HasMany
    {
        return $this->hasMany(DependentRouteModel::class);
    }
}

class RelationshipSourceModelWithMetadata extends Model
{
    protected $table = 'relationship_source_models_with_metadata';

    public $timestamps = false;

    public static ?self $findResult = null;

    public static function find($id, $columns = ['*'])
    {
        return static::$findResult;
    }

    public function getEloquentRelationships(): array
    {
        return [
            'dependentItems' => [
                'relationship_class' => DependentRouteModel::class,
            ],
        ];
    }

    public function dependentItems(): HasMany
    {
        return $this->hasMany(DependentRouteModel::class);
    }
}
