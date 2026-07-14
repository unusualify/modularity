<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Tests\TestCase;

class UrlStaleVariantPurgeTest extends TestCase
{
    private string $urlStalePath;

    private UrlStoreCacheInvalidation $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-url-purge-test-' . uniqid('', true);

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', true);
        Config::set('modularous.cache.presentationItem.store', 'url');

        $this->cacheService = new UrlStoreCacheInvalidation($this->urlStalePath);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        parent::tearDown();
    }

    /** @test */
    public function purge_model_cache_types_clears_all_query_variants_for_the_model(): void
    {
        $model = new BlogLandingTestModel;
        $model->id = 228;
        $model->exists = true;

        $this->seedBlogLandingVariants();

        $this->cacheService->purgeModelCacheTypes($model, [
            'presentationItem' => true,
        ]);

        $urlCache = $this->cacheService->getUrlKeyedStaleCachePublic();
        $this->assertNull($urlCache->get('en', '/blog'));
        $this->assertNull($urlCache->get('en', '/blog?page=2'));
        $this->assertNull($urlCache->get('en', '/blog/search'));
        $this->assertNull($urlCache->get('en', '/blog/search?searchblogtext=press&page=2'));
    }

    /** @test */
    public function refresh_model_caches_clears_query_variants_before_warm(): void
    {
        $model = new BlogLandingTestModel;
        $model->id = 228;
        $model->exists = true;

        $this->seedBlogLandingVariants();

        $this->cacheService->refreshModelCaches($model, [
            'presentationItem' => true,
        ]);

        $urlCache = $this->cacheService->getUrlKeyedStaleCachePublic();
        $this->assertNull($urlCache->get('en', '/blog'));
        $this->assertNull($urlCache->get('en', '/blog?page=2'));
        $this->assertNull($urlCache->get('en', '/blog/search'));
        $this->assertNull($urlCache->get('en', '/blog/search?searchblogtext=press&page=2'));
    }

    /** @test */
    public function invalidate_module_route_clears_all_query_variants_for_the_route(): void
    {
        $this->seedBlogLandingVariants();

        $this->cacheService->invalidateModuleRoute('Blog', 'BlogLanding');

        $urlCache = $this->cacheService->getUrlKeyedStaleCachePublic();
        $this->assertNull($urlCache->get('en', '/blog'));
        $this->assertNull($urlCache->get('en', '/blog?page=2'));
        $this->assertNull($urlCache->get('en', '/blog/search'));
        $this->assertNull($urlCache->get('en', '/blog/search?searchblogtext=press&page=2'));
    }

    /** @test */
    public function invalidate_for_model_with_skip_invalidation_still_clears_query_variants(): void
    {
        $model = new BlogLandingTestModel;
        $model->id = 228;
        $model->exists = true;

        $this->seedBlogLandingVariants();

        $this->cacheService->invalidateForModel($model, [
            'presentationItem' => true,
        ], [
            'warmup' => false,
            'skipInvalidation' => true,
        ]);

        $urlCache = $this->cacheService->getUrlKeyedStaleCachePublic();
        $this->assertNull($urlCache->get('en', '/blog/search?searchblogtext=press&page=2'));
    }

    private function seedBlogLandingVariants(): void
    {
        $meta = [
            'urlable_type' => BlogLandingTestModel::class,
            'urlable_id' => 228,
            'module' => 'Blog',
            'route' => 'BlogLanding',
            'published' => true,
        ];

        $urlCache = $this->cacheService->getUrlKeyedStaleCachePublic();
        $urlCache->put('en', '/blog', '<html>listing</html>', $meta, 900, 3600);
        $urlCache->put('en', '/blog?page=2', '<html>listing-page-two</html>', $meta, 900, 3600);
        $urlCache->put('en', '/blog/search', '<html>search</html>', $meta, 900, 3600);
        $urlCache->put(
            'en',
            '/blog/search?searchblogtext=press&page=2',
            '<html>search-page-two</html>',
            $meta,
            900,
            3600,
        );
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

class UrlStoreCacheInvalidation
{
    use CacheInvalidation;

    protected Repository $store;

    protected string $prefix = 'modularous';

    protected bool $usesTags = true;

    protected StaleFileCache $staleFileCache;

    protected UrlKeyedStaleCache $urlKeyedStaleCache;

    public function __construct(string $urlStalePath)
    {
        $this->store = Cache::store('array');
        $this->staleFileCache = new StaleFileCache(sys_get_temp_dir() . '/modularous-stale-unused-' . uniqid());
        $this->urlKeyedStaleCache = new UrlKeyedStaleCache($urlStalePath);
    }

    public function getUrlKeyedStaleCachePublic(): UrlKeyedStaleCache
    {
        return $this->urlKeyedStaleCache;
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
        return $model instanceof BlogLandingTestModel ? 'Blog' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof BlogLandingTestModel ? 'BlogLanding' : null;
    }

    protected function warmupForModel(Model $model, array $types = [], ?string $moduleName = null, ?string $moduleRouteName = null): void
    {
        // Refresh-only tests assert purge behavior without warming side effects.
    }
}

class BlogLandingTestModel extends Model
{
    protected $table = 'blog_landing_test_models';

    public function getMorphClass(): string
    {
        return self::class;
    }
}
