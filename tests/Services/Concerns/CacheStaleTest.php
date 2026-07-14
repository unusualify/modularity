<?php

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheHelpers;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;

class CacheStaleTest extends TestCase
{
    protected ConcreteStaleCacheHelpers $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.presentationItem.store', 'model');
        Config::set('modularous.cache.presentationItem.swr', true);
        Config::set('modularous.cache.presentationItem.stale_ttl', 3600);

        $this->cacheService = new ConcreteStaleCacheHelpers;
    }

    /** @test */
    public function it_builds_stale_cache_key_with_suffix(): void
    {
        $this->assertSame('fresh-key:stale', $this->cacheService->staleCacheKey('fresh-key'));
    }

    /** @test */
    public function it_puts_and_gets_stale_values(): void
    {
        $this->cacheService->putStaleWithRelations('swr-key', 'stale-html', 3600, ['Page' => 1]);

        $value = $this->cacheService->getStale('swr-key', null, ['Page' => 1]);

        $this->assertSame('stale-html', $value);
    }

    /** @test */
    public function it_forgets_stale_values(): void
    {
        $this->cacheService->putStaleWithRelations('forget-stale', 'value', 3600, ['Page' => 2]);
        $this->assertTrue($this->cacheService->forgetStale('forget-stale', ['Page' => 2]));
        $this->assertNull($this->cacheService->getStale('forget-stale', null, ['Page' => 2]));
    }

    /** @test */
    public function modularous_cache_service_reports_swr_settings(): void
    {
        Config::set('modularous.cache.modules.PrimaryPage.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.types.presentationItem', true);

        $service = new ModularousCacheService;

        $this->assertTrue($service->isSwrEnabled('PrimaryPage', 'Home', 'presentationItem'));
        $this->assertEquals(3600, $service->getStaleTtl('presentationItem'));
        $this->assertFalse($service->isWebhookEnabled());
    }

    /** @test */
    public function facade_exposes_stale_helpers(): void
    {
        ModularousCache::putStaleWithRelations('facade-stale', 'from-facade', 120, ['Page' => 9]);

        $this->assertSame('from-facade', ModularousCache::getStale('facade-stale', null, ['Page' => 9]));
        $this->assertSame('facade-stale:stale', ModularousCache::staleCacheKey('facade-stale'));
    }

    /** @test */
    public function it_keeps_locale_scoped_stale_entries_isolated(): void
    {
        Config::set('app.locales', ['en', 'tr']);

        $enKey = 'modularous:PrimaryPage:Home:presentationItem:5:' . md5(serialize(['locale' => 'en']));
        $trKey = 'modularous:PrimaryPage:Home:presentationItem:5:' . md5(serialize(['locale' => 'tr']));

        $this->cacheService->putStaleWithRelations($enKey, '<html>en</html>', 3600, [
            'Page' => 5,
            StaleFileCache::LOCALE_RELATION_KEY => 'en',
        ]);
        $this->cacheService->putStaleWithRelations($trKey, '<html>tr</html>', 3600, [
            'Page' => 5,
            StaleFileCache::LOCALE_RELATION_KEY => 'tr',
        ]);

        $this->assertSame('<html>en</html>', $this->cacheService->getStale($enKey, null, [
            'Page' => 5,
            StaleFileCache::LOCALE_RELATION_KEY => 'en',
        ]));
        $this->assertSame('<html>tr</html>', $this->cacheService->getStale($trKey, null, [
            'Page' => 5,
            StaleFileCache::LOCALE_RELATION_KEY => 'tr',
        ]));
    }

    /** @test */
    public function it_logs_when_presentation_mirror_write_fails(): void
    {
        Config::set('modularous.cache.presentationItem.store', 'model');
        Config::set('modularous.cache.presentationItem.swr', true);

        $cacheService = new FailingPresentationMirrorCacheHelpers;

        $cacheService->putWithRelations(
            'mirror-key',
            '<html>page</html>',
            300,
            'Blog',
            'Post',
            ['Page' => 1],
            'presentationItem'
        );

        $this->assertTrue(true);
    }
}

class ConcreteStaleCacheHelpers
{
    use CacheHelpers;

    protected Repository $store;

    protected string $prefix = 'modularous';

    protected bool $usesTags = false;

    protected StaleFileCache $staleFileCache;

    protected UrlKeyedStaleCache $urlKeyedStaleCache;

    public function __construct()
    {
        $this->store = Cache::store('array');
        $this->staleFileCache = new StaleFileCache(sys_get_temp_dir() . '/modularous-stale-test-' . uniqid());
        $this->urlKeyedStaleCache = new UrlKeyedStaleCache(sys_get_temp_dir() . '/modularous-url-stale-test-' . uniqid());
    }

    protected function getStaleFileCache(): StaleFileCache
    {
        return $this->staleFileCache;
    }

    protected function getUrlPresentationCacheStore(): UrlPresentationCacheStoreInterface
    {
        return new FileUrlPresentationCacheDriver($this->urlKeyedStaleCache);
    }

    protected function usesFileStaleStore(?string $type = null): bool
    {
        return true;
    }

    protected function isStaleStorageEnabled(?string $type = null): bool
    {
        return true;
    }

    protected function isSwrEnabled(?string $moduleName = null, ?string $moduleRouteName = null, ?string $type = null): bool
    {
        return true;
    }

    protected function getStaleTtl(?string $type = null): int
    {
        return 3600;
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

    protected function getPresentationCacheStore(): string
    {
        return 'model';
    }
}

class FailingPresentationMirrorCacheHelpers extends ConcreteStaleCacheHelpers
{
    public function putStaleWithRelations(
        string $key,
        $value,
        int $ttl,
        array $relations = [],
        ?string $type = null,
    ): bool {
        return false;
    }
}
