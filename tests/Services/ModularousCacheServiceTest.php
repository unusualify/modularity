<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKey;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKeyResolver;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;

class ModularousCacheServiceTest extends TestCase
{
    private string $urlStalePath;

    private string $modelStalePath;

    private ModularousCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-service-url-' . uniqid('', true);
        $this->modelStalePath = sys_get_temp_dir() . '/modularous-service-model-' . uniqid('', true);

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.presentationItem.swr', true);
        Config::set('modularous.cache.presentationItem.serve_first', true);
        Config::set('modularous.cache.presentationItem.stale_ttl', 7200);
        Config::set('modularous.cache.presentationItem.url.driver', 'file');
        Config::set('modularous.cache.presentationItem.url.base_path', $this->urlStalePath);
        Config::set('modularous.cache.presentationItem.model.stale_path', $this->modelStalePath);
        Config::set('modularous.cache.webhook.enabled', true);
        Config::set('modularous.cache.webhook.secret', 'test-secret');
        Config::set('modularous.cache.modules.Blog.enabled', true);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.enabled', true);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.types.presentationItem', true);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.types.counts', true);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.presentation_cache_key', PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.presentation_cache_query', ['page', 'searchblogtext']);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.admin_cache_actions', true);

        $this->app->forgetInstance('modularous.cache');
        $this->cacheService = new ModularousCacheService;
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        $this->deleteDirectory($this->modelStalePath);
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ModularousCacheService::class, $this->cacheService);
    }

    /** @test */
    public function it_returns_correct_config(): void
    {
        $this->assertSame('array', $this->cacheService->getConfig()['driver']);
    }

    /** @test */
    public function it_checks_if_enabled(): void
    {
        $this->assertTrue($this->cacheService->isEnabled());

        Config::set('modularous.cache.enabled', false);
        $this->app->forgetInstance('modularous.cache');

        $this->assertFalse((new ModularousCacheService)->isEnabled());
    }

    /** @test */
    public function it_checks_module_specific_enabled_state(): void
    {
        Config::set('modularous.cache.all_modules', false);
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertTrue($service->isEnabled('TestModule'));
        $this->assertFalse($service->isEnabled('OtherModule'));
    }

    /** @test */
    public function it_generates_correct_cache_key(): void
    {
        $key = $this->cacheService->generateCacheKey('test-module', 'test-route', 'list', ['id' => 1]);

        $this->assertStringStartsWith('modularous:', $key);
        $this->assertStringContainsString('TestModule', $key);
        $this->assertStringContainsString('TestRoute', $key);
        $this->assertStringContainsString('list', $key);
    }

    /** @test */
    public function it_gets_correct_ttl(): void
    {
        Config::set('modularous.cache.ttl.list', 100);
        Config::set('modularous.cache.modules.TestModule.ttl.list', 200);
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertSame(200, $service->getTtl('list', 'TestModule'));
        $this->assertSame(100, $service->getTtl('list', 'OtherModule'));
        $this->assertSame(300, $service->getTtl('show'));
    }

    /** @test */
    public function it_respects_use_tags_config_when_disabled(): void
    {
        Config::set('modularous.cache.use_tags', false);
        $this->app->forgetInstance('modularous.cache');

        $this->assertFalse((new ModularousCacheService)->usesTags());
    }

    /** @test */
    public function it_can_get_cache_store(): void
    {
        $store = $this->cacheService->getStore();

        $this->assertInstanceOf(Repository::class, $store);
    }

    /** @test */
    public function it_normalizes_parameters_for_consistent_hashing(): void
    {
        $reflection = new \ReflectionClass($this->cacheService);
        $method = $reflection->getMethod('normalizeParams');
        $method->setAccessible(true);

        $params1 = ['z' => 3, 'a' => 1, 'b' => 2];
        $params2 = ['a' => 1, 'b' => 2, 'z' => 3];

        $normalized1 = $method->invoke($this->cacheService, $params1);
        $normalized2 = $method->invoke($this->cacheService, $params2);

        $this->assertSame($normalized1, $normalized2);
        $this->assertSame(['a' => 1, 'b' => 2, 'z' => 3], $normalized1);
    }

    /** @test */
    public function it_normalizes_nested_arrays_recursively(): void
    {
        $reflection = new \ReflectionClass($this->cacheService);
        $method = $reflection->getMethod('normalizeParams');
        $method->setAccessible(true);

        $params = [
            'z' => ['nested_z' => 1, 'nested_a' => 2],
            'a' => ['nested_z' => 3, 'nested_a' => 4],
        ];

        $normalized = $method->invoke($this->cacheService, $params);

        $this->assertSame(['a', 'z'], array_keys($normalized));
        $this->assertSame(['nested_a', 'nested_z'], array_keys($normalized['a']));
        $this->assertSame(['nested_a', 'nested_z'], array_keys($normalized['z']));
    }

    /** @test */
    public function it_can_get_stats_for_all_modules(): void
    {
        $redisMock = \Mockery::mock('stdClass');
        $redisMock->shouldReceive('scan')
            ->withAnyArgs()
            ->andReturn([0, []]);
        $redisMock->shouldReceive('zRange')
            ->withAnyArgs()
            ->andReturn([]);

        Redis::shouldReceive('connection')
            ->with('cache')
            ->andReturn($redisMock);

        $stats = $this->cacheService->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('keys_count', $stats);
        $this->assertSame(0, $stats['keys_count']);
    }

    /** @test */
    public function it_can_get_stats_for_specific_module(): void
    {
        Config::set('modularous.cache.modules.TestModule.enabled', true);
        $this->app->forgetInstance('modularous.cache');

        $redisMock = \Mockery::mock('stdClass');
        $redisMock->shouldReceive('scan')
            ->withAnyArgs()
            ->andReturn([0, []]);
        $redisMock->shouldReceive('zRange')
            ->withAnyArgs()
            ->andReturn([]);

        Redis::shouldReceive('connection')
            ->with('cache')
            ->andReturn($redisMock);

        $stats = (new ModularousCacheService)->getStats('TestModule');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('keys_count', $stats);
    }

    /** @test */
    public function it_resolves_url_presentation_cache_store_and_underlying_file_cache(): void
    {
        $service = new ModularousCacheService;

        $store = $service->getUrlPresentationCacheStore();
        $this->assertInstanceOf(FileUrlPresentationCacheDriver::class, $store);
        $this->assertSame($store->underlyingFileCache(), $service->getUrlKeyedStaleCache());
    }

    /** @test */
    public function it_reports_presentation_cache_store_modes(): void
    {
        $service = new ModularousCacheService;

        $this->assertSame('url', $service->getPresentationCacheStore());
        $this->assertTrue($service->isPresentationCacheEnabled());
        $this->assertTrue($service->isUrlStaleEnabled());
        $this->assertFalse($service->isModelStaleEnabled());
        $this->assertTrue($service->isUrlStaleServeFirst());
        $this->assertSame(7200, $service->getUrlStaleTtl());
    }

    /** @test */
    public function it_reads_presentation_cache_key_strategy_and_allowlist_from_route_config(): void
    {
        $service = new ModularousCacheService;

        $this->assertSame(
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
            $service->getPresentationCacheKeyStrategy('Blog', 'BlogLanding'),
        );
        $this->assertSame(
            ['page', 'searchblogtext'],
            $service->getPresentationCacheQueryAllowlist('Blog', 'BlogLanding'),
        );
        $this->assertSame(
            PresentationUrlCacheKey::STRATEGY_PATH_ONLY,
            $service->getPresentationCacheKeyStrategy('Blog', 'MissingRoute'),
        );
    }

    /** @test */
    public function it_builds_presentation_url_cache_key_resolver(): void
    {
        $service = new ModularousCacheService;

        $resolver = $service->getPresentationUrlCacheKeyResolver();

        $this->assertInstanceOf(PresentationUrlCacheKeyResolver::class, $resolver);
    }

    /** @test */
    public function it_reports_admin_cache_actions_for_enabled_routes(): void
    {
        $service = new ModularousCacheService;

        $this->assertTrue($service->hasAdminCacheActions('Blog', 'BlogLanding'));
        $this->assertFalse($service->hasAdminCacheActions('Blog', 'MissingRoute'));
    }

    /** @test */
    public function it_normalizes_cache_types_input_from_all_and_partial_lists(): void
    {
        $service = new ModularousCacheService;

        $all = $service->normalizeCacheTypesInput('all', 'Blog', 'BlogLanding');
        $this->assertTrue($all['counts']);
        $this->assertTrue($all['presentationItem']);

        $partial = $service->normalizeCacheTypesInput(['counts', 'presentationItem', 'unknown'], 'Blog', 'BlogLanding');
        $this->assertTrue($partial['counts']);
        $this->assertFalse($partial['index']);
        $this->assertTrue($partial['presentationItem']);
    }

    /** @test */
    public function it_filters_disabled_types_when_normalizing_cache_types_input(): void
    {
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.types.counts', false);

        $this->app->forgetInstance('modularous.cache');
        $service = new ModularousCacheService;

        $types = $service->normalizeCacheTypesInput(['counts', 'presentationItem'], 'Blog', 'BlogLanding');

        $this->assertFalse($types['counts']);
        $this->assertTrue($types['presentationItem']);
    }

    /** @test */
    public function it_resolves_manual_cache_types_for_admin_actions(): void
    {
        Config::set('modularous.cache.manual_purge', true);
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.purge', [
            'counts' => false,
        ]);

        $this->app->forgetInstance('modularous.cache');
        $service = new ModularousCacheService;

        $manual = $service->resolveManualCacheTypes('Blog', 'BlogLanding');

        $this->assertFalse($manual['counts']);
        $this->assertTrue($manual['presentationItem']);
    }

    /** @test */
    public function it_reports_auto_invalidation_and_cache_type_configuration(): void
    {
        $service = new ModularousCacheService;

        $this->assertTrue($service->shouldAutoInvalidate('Blog', 'BlogLanding', 'counts'));
        $this->assertTrue($service->isCacheTypeConfigured('Blog', 'BlogLanding', 'presentationItem'));
    }

    /** @test */
    public function it_reports_stale_storage_and_webhook_settings(): void
    {
        $service = new ModularousCacheService;

        $this->assertFalse($service->isStaleStorageEnabled('presentationItem'));
        $this->assertTrue($service->usesFileStaleStore());
        $this->assertTrue($service->isWebhookEnabled());
        $this->assertSame('test-secret', $service->getWebhookSecret());
    }

    /** @test */
    public function it_falls_back_to_path_only_for_unknown_presentation_key_strategies(): void
    {
        Config::set('modularous.cache.modules.Blog.routes.BlogLanding.presentation_cache_key', 'unsupported');

        $this->app->forgetInstance('modularous.cache');
        $service = new ModularousCacheService;

        $this->assertSame(
            PresentationUrlCacheKey::STRATEGY_PATH_ONLY,
            $service->getPresentationCacheKeyStrategy('Blog', 'BlogLanding'),
        );
    }

    /** @test */
    public function it_returns_prefix_driver_and_route_cache_config(): void
    {
        Config::set('modularous.cache.prefix', 'custom-prefix');
        Config::set('modularous.cache.driver', 'array');
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertSame('custom-prefix', $service->getPrefix());
        $this->assertSame('array', $service->getDriver());
        $this->assertSame(
            ['page', 'searchblogtext'],
            $service->getRouteCacheConfig('Blog', 'BlogLanding')['presentation_cache_query'] ?? [],
        );
        $this->assertEmpty($service->getRouteCacheConfig(null, null));
    }

    /** @test */
    public function it_exposes_stale_file_cache_instance(): void
    {
        $service = new ModularousCacheService;

        $this->assertSame(
            $service->getStaleFileCache(),
            $service->getStaleFileCache(),
        );
    }

    /** @test */
    public function it_reports_swr_and_stale_ttl_settings(): void
    {
        Config::set('modularous.cache.presentationItem.swr', true);
        Config::set('modularous.cache.presentationItem.stale_ttl', 1234);
        Config::set('modularous.cache.swr.enabled', true);
        Config::set('modularous.cache.swr.types.counts', false);
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertTrue($service->isSwrEnabled('Blog', 'BlogLanding', 'presentationItem'));
        $this->assertFalse($service->isSwrEnabled('Blog', 'BlogLanding', 'counts'));
        $this->assertSame(1234, $service->getStaleTtl('presentationItem'));
        $this->assertSame(1234, $service->getStaleTtl());
    }

    /** @test */
    public function it_disables_swr_when_presentation_cache_store_is_none(): void
    {
        Config::set('modularous.cache.presentationItem.store', 'none');
        Config::set('modularous.cache.presentationItem.swr', true);
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertFalse($service->isPresentationCacheEnabled());
        $this->assertFalse($service->isSwrEnabled('Blog', 'BlogLanding', 'presentationItem'));
    }

    /** @test */
    public function it_respects_observer_auto_invalidate_toggle(): void
    {
        Config::set('modularous.cache.observer.auto_invalidate', false);
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertFalse($service->shouldAutoInvalidate('Blog', 'BlogLanding', 'counts'));
    }

    /** @test */
    public function it_checks_enabled_state_for_specific_cache_types(): void
    {
        $service = new ModularousCacheService;

        $this->assertTrue($service->isEnabled('Blog', 'BlogLanding', 'presentationItem'));
        $this->assertFalse($service->isEnabled('Blog', 'BlogLanding', 'missing-type'));
    }

    /** @test */
    public function it_generates_default_cache_key_without_params(): void
    {
        $service = new ModularousCacheService;

        $this->assertStringEndsWith(':default', $service->generateCacheKey('blog', 'landing', 'list'));
    }

    /** @test */
    public function it_reports_model_stale_storage_when_store_is_model(): void
    {
        Config::set('modularous.cache.presentationItem.store', 'model');
        Config::set('modularous.cache.swr.presentationItem.stale_driver', 'file');
        $this->app->forgetInstance('modularous.cache');

        $service = new ModularousCacheService;

        $this->assertTrue($service->isModelStaleEnabled());
        $this->assertTrue($service->isStaleStorageEnabled('presentationItem'));
        $this->assertTrue($service->usesFileStaleStore('presentationItem'));
    }

    /** @test */
    public function it_returns_tagged_stats_when_tags_are_enabled(): void
    {
        Config::set('modularous.cache.use_tags', true);
        Config::set('modularous.cache.driver', 'array');
        $this->app->forgetInstance('modularous.cache');

        $redisMock = \Mockery::mock('stdClass');
        $redisMock->shouldReceive('scan')
            ->withAnyArgs()
            ->andReturn([0, ['spatie_tests---tag:modularous:Blog:entries']]);
        $redisMock->shouldReceive('zRange')
            ->withAnyArgs()
            ->andReturn(['abc123:modularous:Blog:BlogLanding:counts:default']);

        Redis::shouldReceive('connection')
            ->with('cache')
            ->andReturn($redisMock);

        $service = new ModularousCacheService;
        $stats = $service->getStats('Blog');

        $this->assertTrue($stats['using_tags']);
        $this->assertSame(1, $stats['keys_count']);
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
