<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\ErrorPage;

use Modules\ErrorPage\Entities\ErrorPage;
use Modules\ErrorPage\Support\ErrorPagePresentationCache;
use Modules\ErrorPage\Support\ErrorPageRenderer;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;

final class ErrorPagePresentationCacheTest extends TestCase
{
    private string $stalePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stalePath = sys_get_temp_dir().'/modularous-error-page-stale-'.uniqid('', true);
        mkdir($this->stalePath, 0777, true);

        config([
            'modularous.cms_features.error_pages_enabled' => true,
            'modularous.cms_features.error_pages_cache_enabled' => true,
            'modularous.cache.enabled' => true,
            'modularous.cache.presentationItem.store' => 'model',
            'modularous.cache.presentationItem.model.stale_path' => $this->stalePath,
            'modularous.cache.swr.presentationItem.stale_path' => $this->stalePath,
            'modularous.cache.modules.ErrorPage' => [
                'enabled' => true,
                'routes' => [
                    'ErrorPage' => [
                        'enabled' => true,
                        'rewarmItem' => false,
                        'types' => [
                            'presentationItem' => true,
                            'counts' => false,
                            'index' => false,
                            'record' => false,
                            'formItem' => false,
                            'formattedItem' => false,
                        ],
                    ],
                ],
            ],
        ]);

        $this->app->forgetInstance('modularous.cache');
        $this->app->singleton('modularous.cache', fn () => new ModularousCacheService);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->stalePath)) {
            $this->removeDirectory($this->stalePath);
        }

        parent::tearDown();
    }

    public function test_enabled_respects_cms_features_flag(): void
    {
        $this->assertTrue(ErrorPagePresentationCache::enabled());

        config(['modularous.cms_features.error_pages_cache_enabled' => false]);

        $this->assertFalse(ErrorPagePresentationCache::enabled());
    }

    public function test_enabled_respects_presentation_store_none(): void
    {
        config(['modularous.cache.presentationItem.store' => 'none']);
        $this->app->forgetInstance('modularous.cache');
        $this->app->singleton('modularous.cache', fn () => new ModularousCacheService);

        $this->assertFalse(ErrorPagePresentationCache::enabled());
    }

    public function test_remember_stores_and_serves_model_scoped_html(): void
    {
        $page = $this->page(42, '404');
        $renders = 0;

        $first = ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>cached-404</html>';
        });
        $second = ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>should-not-run</html>';
        });

        $this->assertSame('<html>cached-404</html>', $first);
        $this->assertSame('<html>cached-404</html>', $second);
        $this->assertSame(1, $renders);
    }

    public function test_invalidate_on_save_clears_cached_html(): void
    {
        $page = $this->page(7, '403');

        ErrorPagePresentationCache::remember($page, 'en', static fn (): string => '<html>before</html>');

        $locale = 'en';
        $key = ErrorPagePresentationCache::cacheKey($page, $locale);
        $relations = [ErrorPage::class => 7, StaleFileCache::LOCALE_RELATION_KEY => $locale];
        $this->assertSame(
            '<html>before</html>',
            ModularousCache::getStaleFileCache()->get($key, null, $relations),
        );

        // Simulate Eloquent saved without DB: invoke invalidate the same way booted() does.
        ErrorPagePresentationCache::invalidate($page);

        $this->assertNull(ModularousCache::getStaleFileCache()->get($key, null, $relations));

        $renders = 0;
        $after = ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>after</html>';
        });

        $this->assertSame('<html>after</html>', $after);
        $this->assertSame(1, $renders);
    }

    public function test_admin_purge_clears_presentation_html_when_store_is_url(): void
    {
        // Mimic production default: presentationItem.store=url — ErrorPage still writes
        // model-scoped StaleFileCache files that generic store-gated purge used to skip.
        config(['modularous.cache.presentationItem.store' => 'url']);
        $this->app->forgetInstance('modularous.cache');
        $this->app->singleton('modularous.cache', fn () => new ModularousCacheService);

        $page = $this->page(1, '404');

        ErrorPagePresentationCache::remember($page, 'en', static fn (): string => '<html>purge-me</html>');

        $locale = 'en';
        $key = ErrorPagePresentationCache::cacheKey($page, $locale);
        $relations = [ErrorPage::class => 1, StaleFileCache::LOCALE_RELATION_KEY => $locale];
        $this->assertSame(
            '<html>purge-me</html>',
            ModularousCache::getStaleFileCache()->get($key, null, $relations),
        );

        // Same path as ManageResourceCache::cachePurge → ModularousCache::purgeModelCacheTypes
        ModularousCache::purgeModelCacheTypes(
            $page,
            ['presentationItem' => true],
            ErrorPagePresentationCache::MODULE,
            ErrorPagePresentationCache::ROUTE,
        );

        $this->assertNull(ModularousCache::getStaleFileCache()->get($key, null, $relations));

        $renders = 0;
        $after = ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>rebuilt</html>';
        });

        $this->assertSame('<html>rebuilt</html>', $after);
        $this->assertSame(1, $renders);
    }

    public function test_warm_writes_presentation_html_after_purge(): void
    {
        $this->registerWarmRenderViews();

        $page = $this->page(11, '404');
        $locale = 'en';
        $key = ErrorPagePresentationCache::cacheKey($page, $locale);
        $relations = [ErrorPage::class => 11, StaleFileCache::LOCALE_RELATION_KEY => $locale];

        ErrorPagePresentationCache::remember($page, $locale, static fn (): string => '<html>stale</html>');
        $this->assertSame(
            '<html>stale</html>',
            ModularousCache::getStaleFileCache()->get($key, null, $relations),
        );

        ModularousCache::purgeModelCacheTypes(
            $page,
            ['presentationItem' => true],
            ErrorPagePresentationCache::MODULE,
            ErrorPagePresentationCache::ROUTE,
        );
        $this->assertNull(ModularousCache::getStaleFileCache()->get($key, null, $relations));

        $this->assertTrue(ErrorPagePresentationCache::warm($page, $locale));

        $cached = ModularousCache::getStaleFileCache()->get($key, null, $relations);
        $this->assertIsString($cached);
        $this->assertStringContainsString('WARM-BODY-404', $cached);

        // Subsequent remember must hit without re-rendering.
        $renders = 0;
        $hit = ErrorPagePresentationCache::remember($page, $locale, function () use (&$renders): string {
            $renders++;

            return '<html>should-not-run</html>';
        });
        $this->assertSame($cached, $hit);
        $this->assertSame(0, $renders);
    }

    public function test_warmup_presentation_item_delegates_to_error_page_cache(): void
    {
        $this->registerWarmRenderViews();

        // Production default store=url — generic warm must still fill model-scoped ErrorPage cache.
        config(['modularous.cache.presentationItem.store' => 'url']);
        $this->app->forgetInstance('modularous.cache');
        $this->app->singleton('modularous.cache', fn () => new ModularousCacheService);

        $page = $this->page(12, '404');
        $locale = 'en';
        $key = ErrorPagePresentationCache::cacheKey($page, $locale);
        $relations = [ErrorPage::class => 12, StaleFileCache::LOCALE_RELATION_KEY => $locale];

        $this->assertNull(ModularousCache::getStaleFileCache()->get($key, null, $relations));

        // Same path as ManageResourceCache::cacheWarm → refreshModelCaches → warmupPresentationItem
        $this->assertTrue(ModularousCache::warmupPresentationItem(
            ErrorPagePresentationCache::MODULE,
            ErrorPagePresentationCache::ROUTE,
            $page,
            $locale,
        ));

        $cached = ModularousCache::getStaleFileCache()->get($key, null, $relations);
        $this->assertIsString($cached);
        $this->assertStringContainsString('WARM-BODY-404', $cached);
    }

    public function test_warm_skips_unpublished_error_page(): void
    {
        $this->registerWarmRenderViews();

        $page = $this->page(13, '404');
        $page->published = false;

        $this->assertFalse(ErrorPagePresentationCache::warm($page, 'en'));

        $locale = 'en';
        $key = ErrorPagePresentationCache::cacheKey($page, $locale);
        $relations = [ErrorPage::class => 13, StaleFileCache::LOCALE_RELATION_KEY => $locale];
        $this->assertNull(ModularousCache::getStaleFileCache()->get($key, null, $relations));
    }

    public function test_remember_bypasses_when_cache_disabled(): void
    {
        config(['modularous.cms_features.error_pages_cache_enabled' => false]);

        $page = $this->page(3, '500');
        $renders = 0;

        ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>a</html>';
        });
        ErrorPagePresentationCache::remember($page, 'en', function () use (&$renders): string {
            $renders++;

            return '<html>b</html>';
        });

        $this->assertSame(2, $renders);
    }

    private function registerWarmRenderViews(): void
    {
        config([
            'modularous.cms_features.error_pages_enabled' => true,
        ]);

        // Avoid LayoutBuilder DB / ModularousVite in package tests — warm only needs HTML output.
        $this->app->instance(ErrorPageRenderer::class, new class
        {
            public function renderPublishedHtml(ErrorPage $item, $request = null): ?string
            {
                if (! (bool) $item->published) {
                    return null;
                }

                return '<html>WARM-BODY-'.(string) $item->error_code.'</html>';
            }
        });
    }

    private function page(int $id, string $errorCode): ErrorPage
    {
        $page = new ErrorPage([
            'name' => 'Error '.$errorCode,
            'error_code' => $errorCode,
            'published' => true,
        ]);
        $page->id = $id;
        $page->exists = true;

        return $page;
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
