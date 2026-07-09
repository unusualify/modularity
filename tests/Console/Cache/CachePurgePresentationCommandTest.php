<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use TestModules\TestModule\Entities\Item;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Jobs\Cache\PurgeModulePresentationCachesJob;
use Unusualify\Modularous\Jobs\Cache\PurgePresentationItemJob;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Tests\MockModuleManager;
use Unusualify\Modularous\Tests\Support\IsolatedTestModules;
use Unusualify\Modularous\Tests\TestCase;

class CachePurgePresentationCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $fixturesPath = IsolatedTestModules::path();
        IsolatedTestModules::seedRoutesStatuses();
        $app['config']->set('modules.paths.modules', $fixturesPath);
        $app['config']->set('modules.scan.paths', [$fixturesPath]);
        $app['config']->set('modules.namespace', 'TestModules');
        $app['config']->set('modules.paths.generator.model', [
            'path' => 'Entities',
            'namespace' => 'Entities',
            'generate' => false,
        ]);

        Modularous::boot();
    }

    protected function setUp(): void
    {
        parent::setUp();

        MockModuleManager::initialize();
        IsolatedTestModules::seedRoutesStatuses(['TestModule' => ['Item' => true]]);

        Schema::dropIfExists('test_module_items');
        Schema::create('test_module_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.all_modules', true);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.enabled', true);
        Config::set('modularous.cache.modules.TestModule.routes.Item.types.presentationItem', true);

        $this->app->forgetInstance('modularous.cache');

        Item::query()->create(['name' => 'Alpha']);
        Item::query()->create(['name' => 'Beta']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_module_items');

        parent::tearDown();
    }

    /** @test */
    public function it_fails_when_caching_is_disabled(): void
    {
        Config::set('modularous.cache.enabled', false);
        $this->app->forgetInstance('modularous.cache');

        $this->artisan('modularous:cache:purge-presentation')
            ->expectsOutputToContain('Caching is disabled')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_when_presentation_cache_store_is_none(): void
    {
        Config::set('modularous.cache.presentationItem.store', 'none');
        $this->app->forgetInstance('modularous.cache');

        $this->artisan('modularous:cache:purge-presentation')
            ->expectsOutputToContain('Presentation cache store is disabled')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_when_route_is_passed_without_module(): void
    {
        $this->artisan('modularous:cache:purge-presentation', ['--route' => 'Item'])
            ->expectsOutputToContain('The --route option requires --module')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_when_id_is_not_numeric(): void
    {
        $this->artisan('modularous:cache:purge-presentation', ['--id' => 'abc'])
            ->expectsOutputToContain('The --id option must be numeric')
            ->assertExitCode(1);
    }

    /** @test */
    public function dry_run_prints_summary_after_queue_mode_preview(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $exitCode = Artisan::call('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Dry run complete. Would process 2 record(s)', $output, $output);
    }

    /** @test */
    public function dry_run_lists_route_job_without_dispatching(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $this->artisan('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Dry run — listing targets that would be purged.')
            ->expectsOutputToContain('Would queue PurgeModulePresentationCachesJob: TestModule::Item (2 records)')
            ->expectsOutputToContain('[queue, locale=all]');

        Bus::assertNotDispatched(PurgeModulePresentationCachesJob::class);
        Bus::assertNotDispatched(PurgePresentationItemJob::class);
    }

    /** @test */
    public function dry_run_sync_mode_lists_purge_without_deleting_files(): void
    {
        Bus::fake();

        $this->artisan('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--sync' => true,
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Would purge presentationItem: TestModule::Item (2 records)')
            ->expectsOutputToContain('[sync, locale=all]');

        Bus::assertNothingDispatched();
    }

    /** @test */
    public function dry_run_with_id_and_locale_filters_output(): void
    {
        Bus::fake();
        Config::set('queue.default', 'database');
        Config::set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);

        $record = Item::query()->orderBy('id')->firstOrFail();

        $this->artisan('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--id' => (string) $record->getKey(),
            '--locale' => 'en',
            '--dry-run' => true,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Would queue PurgePresentationItemJob: TestModule::Item (1 records)')
            ->expectsOutputToContain('[queue, locale=en]');

        Bus::assertNotDispatched(PurgePresentationItemJob::class);
    }

    /** @test */
    public function dry_run_verbose_lists_each_record_line_in_sync_mode(): void
    {
        Bus::fake();

        $records = Item::query()->orderBy('id')->get();

        $command = $this->artisan('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--sync' => true,
            '--dry-run' => true,
            '--verbose' => true,
        ])->assertExitCode(0);

        foreach ($records as $record) {
            $command->expectsOutputToContain(
                "Would purge presentationItem: TestModule::Item Item (id={$record->getKey()}), locale=all [sync]",
            );
        }
    }

    /** @test */
    public function sync_mode_purges_presentation_files_for_each_record(): void
    {
        $urlStalePath = sys_get_temp_dir() . '/modularous-purge-cmd-url-' . uniqid('', true);
        $modelStalePath = sys_get_temp_dir() . '/modularous-purge-cmd-model-' . uniqid('', true);

        Config::set('modularous.cache.presentationItem.url.base_path', $urlStalePath);
        Config::set('modularous.cache.presentationItem.model.stale_path', $modelStalePath);
        $this->app->forgetInstance('modularous.cache');

        $record = Item::query()->orderBy('id')->firstOrFail();

        $urlCache = new UrlKeyedStaleCache($urlStalePath);
        $meta = [
            'urlable_type' => Item::class,
            'urlable_id' => $record->getKey(),
            'module' => 'TestModule',
            'route' => 'Item',
            'published' => true,
        ];
        $urlCache->put('en', '/items/alpha', '<html>url</html>', $meta, 900, 3600);

        $modelStale = new StaleFileCache($modelStalePath);
        $key = 'modularous:TestModule:Item:presentationItem:' . $record->getKey() . ':' . md5(serialize(['locale' => 'en']));
        $modelStale->put($key, '<html>model</html>', 3600, [Item::class => $record->getKey()]);

        $this->artisan('modularous:cache:purge-presentation', [
            '--module' => 'TestModule',
            '--route' => 'Item',
            '--sync' => true,
        ])->assertExitCode(0);

        $this->assertNull($urlCache->get('en', '/items/alpha'));
        $this->assertNull($modelStale->get($key));

        $this->deleteDirectory($urlStalePath);
        $this->deleteDirectory($modelStalePath);
    }

    /** @test */
    public function purge_presentation_item_for_model_clears_both_filesystem_layers(): void
    {
        $urlStalePath = sys_get_temp_dir() . '/modularous-purge-svc-url-' . uniqid('', true);
        $modelStalePath = sys_get_temp_dir() . '/modularous-purge-svc-model-' . uniqid('', true);

        $service = new PurgePresentationCommandCacheService($urlStalePath, $modelStalePath);

        $model = new Item;
        $model->id = 44;
        $model->exists = true;

        $urlCache = new UrlKeyedStaleCache($urlStalePath);
        $meta = [
            'urlable_type' => Item::class,
            'urlable_id' => 44,
            'module' => 'TestModule',
            'route' => 'Item',
            'published' => true,
        ];
        $urlCache->put('en', '/items/forty-four', '<html>url</html>', $meta, 900, 3600);

        $modelStale = new StaleFileCache($modelStalePath);
        $key = 'modularous:TestModule:Item:presentationItem:44:' . md5(serialize(['locale' => 'en']));
        $modelStale->put($key, '<html>model</html>', 3600, [Item::class => 44]);

        $deleted = $service->purgePresentationItemForModel($model, 'TestModule', 'Item');

        $this->assertGreaterThanOrEqual(2, $deleted);
        $this->assertNull($urlCache->get('en', '/items/forty-four'));
        $this->assertNull($modelStale->get($key));

        $this->deleteDirectory($urlStalePath);
        $this->deleteDirectory($modelStalePath);
    }

    /** @test */
    public function purge_presentation_item_for_module_route_clears_route_scoped_files(): void
    {
        $urlStalePath = sys_get_temp_dir() . '/modularous-purge-route-url-' . uniqid('', true);
        $modelStalePath = sys_get_temp_dir() . '/modularous-purge-route-model-' . uniqid('', true);

        $service = new PurgePresentationCommandCacheService($urlStalePath, $modelStalePath);

        $urlCache = new UrlKeyedStaleCache($urlStalePath);
        $meta = [
            'urlable_type' => Item::class,
            'urlable_id' => 10,
            'module' => 'TestModule',
            'route' => 'Item',
            'published' => true,
        ];
        $urlCache->put('en', '/items', '<html>listing</html>', $meta, 900, 3600);
        $urlCache->put('en', '/items?page=2', '<html>page-two</html>', $meta, 900, 3600);

        $modelStale = new StaleFileCache($modelStalePath);
        $key = 'modularous:TestModule:Item:presentationItem:10:' . md5(serialize(['locale' => 'en']));
        $modelStale->put($key, '<html>model</html>', 3600, [Item::class => 10]);

        $deleted = $service->purgePresentationItemForModuleRoute('TestModule', 'Item');

        $this->assertGreaterThanOrEqual(2, $deleted);
        $this->assertNull($urlCache->get('en', '/items'));
        $this->assertNull($urlCache->get('en', '/items?page=2'));
        $this->assertNull($modelStale->get($key));

        $this->deleteDirectory($urlStalePath);
        $this->deleteDirectory($modelStalePath);
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}

class PurgePresentationCommandCacheService
{
    use CacheInvalidation;

    protected Repository $store;

    protected string $prefix = 'modularous';

    protected bool $usesTags = false;

    protected StaleFileCache $staleFileCache;

    protected UrlKeyedStaleCache $urlKeyedStaleCache;

    public function __construct(string $urlStalePath, string $modelStalePath)
    {
        $this->store = Cache::store('array');
        $this->staleFileCache = new StaleFileCache($modelStalePath);
        $this->urlKeyedStaleCache = new UrlKeyedStaleCache($urlStalePath);
    }

    protected function getStaleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    protected function getUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        return new FileUrlPresentationCacheDriver($this->urlKeyedStaleCache);
    }

    protected function getPresentationCacheStore(): string
    {
        return 'url';
    }

    protected function getStore(): Repository
    {
        return $this->store;
    }

    protected function getPrefix(): string
    {
        return $this->prefix;
    }

    protected function usesTags(): bool
    {
        return $this->usesTags;
    }

    protected function isEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        return true;
    }

    protected function getModuleNameFromModel(Model $model): ?string
    {
        return $model instanceof Item ? 'TestModule' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof Item ? 'Item' : null;
    }

    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null, ?string $locale = null): void
    {
    }
}
