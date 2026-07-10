<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsVisitorRequestContextResolverInterface;
use Modules\Cms\Entities\Page;
use Modules\Cms\Http\Middleware\ServeUrlKeyedStaleMiddleware;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\StalePublicationMeta;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Services\Concerns\CacheInvalidation;
use Unusualify\Modularous\Tests\TestCase;

class PresentationItemUrlWarmTest extends TestCase
{
    private string $urlStalePath;

    private ConcreteUrlStoreCacheInvalidation $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-url-warm-test-' . uniqid('', true);

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', true);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.presentationItem.serve_first', true);
        Config::set('modularous.cache.presentationItem.url.base_path', $this->urlStalePath);
        Config::set('modularous.cache.modules.BusinessPackage.enabled', true);
        Config::set('modularous.cache.modules.BusinessPackage.routes.PackageCountry.enabled', true);
        Config::set('modularous.cache.modules.BusinessPackage.routes.PackageCountry.types.presentationItem', true);

        $this->cacheService = new ConcreteUrlStoreCacheInvalidation($this->urlStalePath);
        $this->app->forgetInstance('modularous.cache');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        parent::tearDown();
    }

    /** @test */
    public function purge_then_url_store_rewrite_leaves_middleware_serving_200(): void
    {
        $paths = [
            'en' => '/country-pr-packages/argentina',
            'tr' => '/ulke-pr-paketleri/arjantin',
            'nl' => '/land-pr-pakketten/argentinie',
        ];
        $html = '<!DOCTYPE html><html><body>package-country</body></html>';
        $item = new Page;
        $item->id = 44;
        $item->published = true;

        $urlCache = ModularousCache::getUrlKeyedStaleCache();

        foreach ($paths as $locale => $path) {
            $this->writePackageCountryUrlEntry($urlCache, $locale, $path, $html, $item);
            $this->assertNotNull($urlCache->get($locale, $path));
        }

        foreach ($paths as $locale => $path) {
            $this->assertTrue($urlCache->forget($locale, $path));
            $this->assertNull($urlCache->get($locale, $path));
        }

        foreach ($paths as $locale => $path) {
            $this->writePackageCountryUrlEntry($urlCache, $locale, $path, $html, $item);
            $this->assertNotNull($urlCache->get($locale, $path));
        }

        $resolver = new class($paths) implements CmsVisitorRequestContextResolverInterface
        {
            /** @param array<string, string> $paths */
            public function __construct(private array $paths) {}

            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                foreach ($this->paths as $locale => $path) {
                    if ($request->getPathInfo() === $path || $request->getPathInfo() === '/' . trim($locale, '/') . $path) {
                        return [$locale, $path, $locale !== 'en'];
                    }
                }

                return ['en', $this->paths['en'], false];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = new ServeUrlKeyedStaleMiddleware($resolver, $localization);

        foreach ($paths as $locale => $path) {
            $requestPath = $locale === 'en' ? $path : '/' . trim($locale, '/') . $path;
            $response = $middleware->handle(
                Request::create($requestPath, 'GET'),
                fn () => abort(404, 'controller-should-not-run'),
            );

            $this->assertSame(200, $response->getStatusCode(), "Expected 200 for {$locale} {$path}");
            $this->assertSame($html, $response->getContent());
            $this->assertSame(
                CmsPublicPresentationItemCache::cacheHeaderValue(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT),
                $response->headers->get('X-Modularous-Cache'),
            );
        }
    }

    /** @test */
    public function store_url_keyed_presentation_writes_visible_meta_for_package_country_paths(): void
    {
        $item = new Page;
        $item->id = 44;
        $item->published = true;
        $path = '/country-pr-packages/argentina';
        $html = '<!DOCTYPE html><html><body>warm-bypass</body></html>';

        $this->assertTrue(CmsPublicPresentationItemCache::storeUrlKeyedPresentation(
            'en',
            $path,
            $html,
            $item,
            'BusinessPackage',
            'PackageCountry',
        ));

        $entry = ModularousCache::getUrlKeyedStaleCache()->get('en', $path);
        $this->assertNotNull($entry);
        $this->assertSame($html, $entry['html']);
        $this->assertTrue($entry['meta']['published'] ?? false);
        $this->assertSame(StalePublicationMeta::PROFILE_STANDARD, $entry['meta']['visibility_profile']);
        $this->assertSame($path, $entry['meta']['normalized_path']);
    }

    /** @test */
    public function forget_presentation_stale_for_model_clears_url_stale_files(): void
    {
        $item = new Page;
        $item->id = 44;
        $path = '/country-pr-packages/argentina';

        $urlCache = ModularousCache::getUrlKeyedStaleCache();
        $urlCache->put('en', $path, '<html>old</html>', [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            'urlable_type' => Page::class,
            'urlable_id' => 44,
            'module' => 'BusinessPackage',
            'route' => 'PackageCountry',
        ], 900, 3600);
        $urlCache->put('en', $path . '?page=2', '<html>old-page-two</html>', [
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            'urlable_type' => Page::class,
            'urlable_id' => 44,
            'module' => 'BusinessPackage',
            'route' => 'PackageCountry',
        ], 900, 3600);

        $this->cacheService->forgetPresentationStaleForModelPublic($item);

        $this->assertNull($urlCache->get('en', $path));
        $this->assertNull($urlCache->get('en', $path . '?page=2'));
    }

    private function writePackageCountryUrlEntry(
        UrlKeyedStaleCache $urlCache,
        string $locale,
        string $path,
        string $html,
        Page $item,
    ): void {
        $this->assertTrue(CmsPublicPresentationItemCache::storeUrlKeyedPresentation(
            $locale,
            $path,
            $html,
            $item,
            'BusinessPackage',
            'PackageCountry',
        ));
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

class ConcreteUrlStoreCacheInvalidation
{
    use CacheInvalidation {
        forgetPresentationStaleForModel as public forgetPresentationStaleForModelPublic;
    }

    protected Repository $store;

    protected string $prefix = 'modularous';

    protected bool $usesTags = false;

    protected UrlKeyedStaleCache $urlKeyedStaleCache;

    public function __construct(string $urlStalePath)
    {
        $this->store = Cache::store('array');
        $this->urlKeyedStaleCache = new UrlKeyedStaleCache($urlStalePath);
    }

    protected function getStaleFileCache(): StaleFileCache
    {
        return new StaleFileCache(sys_get_temp_dir() . '/unused-stale');
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
        return $model instanceof Page ? 'BusinessPackage' : null;
    }

    protected function getModuleRouteNameFromModel(Model $model): ?string
    {
        return $model instanceof Page ? 'PackageCountry' : null;
    }
}
