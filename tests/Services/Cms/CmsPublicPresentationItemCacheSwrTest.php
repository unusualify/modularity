<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Modules\Cms\Entities\Page;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPresentationItemCacheSwrTest extends TestCase
{
    private string $stalePath;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->stalePath = sys_get_temp_dir() . '/modularous-swr-test-' . uniqid('', true);

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', true);
        Config::set('modularous.cache.presentationItem.store', 'model');
        Config::set('modularous.cache.presentationItem.swr', true);
        Config::set('modularous.cache.presentationItem.model.stale_path', $this->stalePath);
        Config::set('modularous.cache.presentationItem.url.base_path', sys_get_temp_dir() . '/modularous-swr-url-' . uniqid('', true));
        Config::set('modularous.cache.modules.PrimaryPage.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.types.presentationItem', true);

        $this->app->forgetInstance('modularous.cache');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->stalePath);
        parent::tearDown();
    }

    /** @test */
    public function it_serves_stale_html_from_file_on_fresh_miss_and_dispatches_warm_job(): void
    {
        Bus::fake();

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 101;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            $locale,
        );
        $staleHtml = '<!DOCTYPE html><html><body>stale-from-file</body></html>';

        ModularousCache::putStaleWithRelations(
            $cacheKey,
            $staleHtml,
            86400,
            [Page::class => $item->id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $result = CmsPublicPresentationItemCache::resolvePresentationHtml(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
        );

        $this->assertSame(CmsPublicPresentationItemCache::CACHE_STATUS_STALE, $result['status']);
        $this->assertSame($staleHtml, $result['html']);
        $this->assertSame(
            'presentationItem=STALE',
            CmsPublicPresentationItemCache::cacheHeaderValue($result['status']),
        );

        Bus::assertDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function stale_hit_does_not_dispatch_duplicate_warm_jobs_within_overlap_window(): void
    {
        Bus::fake();

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 102;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            $locale,
        );
        $staleHtml = '<!DOCTYPE html><html><body>stale-from-file</body></html>';
        $relations = [Page::class => $item->id];

        ModularousCache::putStaleWithRelations(
            $cacheKey,
            $staleHtml,
            86400,
            CmsPublicPresentationItemCache::staleRelations($relations, $locale),
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $lockKey = CmsPublicPresentationItemCache::warmPresentationOverlapKey($item, $moduleName, $routeName);
        Cache::put($lockKey, 1, 600);

        CmsPublicPresentationItemCache::resolvePresentationHtml(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
        );

        Bus::assertNotDispatched(WarmPresentationItemJob::class);
    }

    /** @test */
    public function stale_hit_dispatches_warm_job_only_once_for_multiple_locales(): void
    {
        Bus::fake();

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 103;
        $viewName = 'cms::page.custom';
        $relations = [Page::class => $item->id];
        $staleHtml = '<!DOCTYPE html><html><body>stale</body></html>';

        foreach (['en', 'nl', 'tr'] as $locale) {
            $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                $moduleName,
                $routeName,
                $item,
                $viewName,
                $locale,
            );

            ModularousCache::putStaleWithRelations(
                $cacheKey,
                $staleHtml,
                86400,
                CmsPublicPresentationItemCache::staleRelations($relations, $locale),
                CmsPublicPresentationItemCache::CACHE_TYPE,
            );
        }

        foreach (['en', 'nl', 'tr'] as $locale) {
            CmsPublicPresentationItemCache::resolvePresentationHtml(
                $moduleName,
                $routeName,
                $item,
                $viewName,
                ['item' => $item],
                $locale,
            );
        }

        Bus::assertDispatchedTimes(WarmPresentationItemJob::class, 1);
    }

    /** @test */
    public function it_writes_fresh_to_redis_and_stale_to_file(): void
    {
        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 202;
        $viewName = 'cms::page.custom';
        $locale = 'en';
        $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            $locale,
        );
        $html = '<!DOCTYPE html><html><body>fresh-and-stale</body></html>';

        ModularousCache::putWithRelations(
            $cacheKey,
            $html,
            60,
            $moduleName,
            $routeName,
            [Page::class => $item->id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );
        ModularousCache::putStaleWithRelations(
            $cacheKey,
            $html,
            86400,
            [Page::class => $item->id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $this->assertSame($html, ModularousCache::getWithRelations(
            $cacheKey,
            null,
            $moduleName,
            $routeName,
            [Page::class => $item->id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        ));
        $this->assertSame($html, ModularousCache::getStale(
            $cacheKey,
            null,
            [Page::class => $item->id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        ));
        $this->assertNull(ModularousCache::get($cacheKey . ':stale'));
    }

    /** @test */
    public function it_writes_separate_stale_files_per_locale(): void
    {
        Config::set('app.locales', ['en', 'tr']);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $item = new Page;
        $item->id = 303;
        $viewName = 'cms::page.custom';
        $relations = [Page::class => $item->id];

        foreach (['en', 'tr'] as $locale) {
            $cacheKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                $moduleName,
                $routeName,
                $item,
                $viewName,
                $locale,
            );
            $html = "<!DOCTYPE html><html><body>{$locale}</body></html>";

            ModularousCache::putStaleWithRelations(
                $cacheKey,
                $html,
                86400,
                CmsPublicPresentationItemCache::staleRelations($relations, $locale),
                CmsPublicPresentationItemCache::CACHE_TYPE,
            );
        }

        $enKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            'en',
        );
        $trKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            'tr',
        );

        $enHash = mb_substr($enKey, mb_strrpos($enKey, ':') + 1);
        $trHash = mb_substr($trKey, mb_strrpos($trKey, ':') + 1);

        $this->assertFileExists($this->stalePath . '/PrimaryPage/Home/Page/303/en/' . $enHash . '.html');
        $this->assertFileExists($this->stalePath . '/PrimaryPage/Home/Page/303/tr/' . $trHash . '.html');
        $this->assertNotSame($enHash, $trHash);

        $this->assertSame(
            '<!DOCTYPE html><html><body>en</body></html>',
            ModularousCache::getStale($enKey, null, CmsPublicPresentationItemCache::staleRelations($relations, 'en'), CmsPublicPresentationItemCache::CACHE_TYPE),
        );
        $this->assertSame(
            '<!DOCTYPE html><html><body>tr</body></html>',
            ModularousCache::getStale($trKey, null, CmsPublicPresentationItemCache::staleRelations($relations, 'tr'), CmsPublicPresentationItemCache::CACHE_TYPE),
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
