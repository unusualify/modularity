<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Config;
use Modules\Cms\Entities\Page;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\StalePublicationMeta;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPresentationItemCacheResilienceTest extends TestCase
{
    private string $urlStalePath;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-resilience-test-' . uniqid('', true);

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.presentationItem.url.base_path', $this->urlStalePath);
        Config::set('modularous.cache.modules.PrimaryPage.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.types.presentationItem', true);

        $this->app->forgetInstance('modularous.cache');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        parent::tearDown();
    }

    /** @test */
    public function bypass_swr_writes_url_keyed_file(): void
    {
        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 501;
        $item->published = true;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $path = '/pages/home';
        $html = '<!DOCTYPE html><html><body>bypass-write</body></html>';

        CmsPublicPresentationItemCache::storeUrlKeyedPresentation(
            $locale,
            $path,
            $html,
            $item,
            $moduleName,
            $routeName,
        );

        $entry = ModularousCache::getUrlKeyedStaleCache()->get($locale, $path);
        $this->assertNotNull($entry);
        $this->assertSame($html, $entry['html']);
        $this->assertSame(StalePublicationMeta::PROFILE_STANDARD, $entry['meta']['visibility_profile']);
        $this->assertSame('PrimaryPage', $entry['meta']['module']);
        $this->assertSame('Home', $entry['meta']['route']);
    }

    /** @test */
    public function resolve_reads_url_keyed_file_on_hit(): void
    {
        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 502;
        $item->published = true;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $path = '/pages/cached';
        $html = '<!DOCTYPE html><html><body>url-hit</body></html>';

        CmsPublicPresentationItemCache::storeUrlKeyedPresentation(
            $locale,
            $path,
            $html,
            $item,
            $moduleName,
            $routeName,
        );

        $result = CmsPublicPresentationItemCache::resolvePresentationHtml(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
            normalizedPath: $path,
        );

        $this->assertSame(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT, $result['status']);
        $this->assertSame($html, $result['html']);
    }

    /** @test */
    public function url_store_serves_url_stale_instead_of_id_based_stale_when_both_exist(): void
    {
        Config::set('modularous.cache.presentationItem.swr', true);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 503;
        $item->published = true;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $path = '/pages/no-id-stale';
        $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            $locale,
        );
        $urlHtml = '<!DOCTYPE html><html><body>url-stale-wins</body></html>';
        $idHtml = '<!DOCTYPE html><html><body>id-stale-should-not-win</body></html>';

        ModularousCache::getUrlKeyedStaleCache()->put(
            $locale,
            $path,
            $urlHtml,
            StalePublicationMeta::fromModel(
                $item,
                $locale,
                $path,
                $moduleName,
                $routeName,
                1,
            ),
            1,
            86400,
        );

        ModularousCache::putStaleWithRelations(
            $cacheKey,
            $idHtml,
            86400,
            CmsPublicPresentationItemCache::staleRelations([Page::class => $item->id], $locale),
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $result = CmsPublicPresentationItemCache::resolvePresentationHtml(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
            normalizedPath: $path,
        );

        $this->assertContains($result['status'], [
            CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT,
            CmsPublicPresentationItemCache::CACHE_STATUS_URL_STALE,
        ]);
        $this->assertSame($urlHtml, $result['html']);
        $this->assertNotSame($idHtml, $result['html']);
    }

    /** @test */
    public function url_store_with_swr_does_not_write_id_based_stale_files(): void
    {
        Config::set('modularous.cache.presentationItem.swr', true);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 504;
        $item->published = true;
        $locale = 'en';
        $path = '/pages/write-url-only';
        $html = '<!DOCTYPE html><html><body>url-only-write</body></html>';

        CmsPublicPresentationItemCache::storeUrlKeyedPresentation(
            $locale,
            $path,
            $html,
            $item,
            $moduleName,
            $routeName,
        );

        $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            'cms::page.custom',
            $locale,
        );

        $this->assertNull(ModularousCache::getStale(
            $cacheKey,
            null,
            CmsPublicPresentationItemCache::staleRelations([Page::class => $item->id], $locale),
            CmsPublicPresentationItemCache::CACHE_TYPE,
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
