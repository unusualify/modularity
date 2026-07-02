<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Config;
use Modules\Cms\Entities\Page;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPresentationItemCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicPresentationItemCache::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        Config::set('modularous.cache.driver', 'array');
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.use_tags', false);
        Config::set('modularous.cache.all_modules', false);
        Config::set('modularous.cache.modules.PrimaryPage.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.enabled', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.types.presentationItem', true);
        Config::set('modularous.cache.modules.PrimaryPage.routes.Home.types.formItem', false);
    }

    /** @test */
    public function cache_key_for_public_presentation_matches_wrapped_or_full_branch(): void
    {
        $item = new Page;
        $item->id = 99;
        $viewName = 'cms::page.custom';

        $resolvedKey = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            'BusinessPackage',
            'PackageCountry',
            $item,
            $viewName,
            'en',
        );

        $usesShell = \Modules\Cms\Support\CmsPageLayoutPresentationWrapper::resolvesWithPageLayoutShell($item, $viewName);
        $expectedKey = $usesShell
            ? CmsPublicPresentationItemCache::cacheKey('BusinessPackage', 'PackageCountry', 99, 'en')
            : CmsPublicPresentationItemCache::cacheKey('BusinessPackage', 'PackageCountry', 99, 'en', ['full' => true]);

        $this->assertSame($expectedKey, $resolvedKey);
    }

    /** @test */
    public function remember_public_presentation_returns_cached_document_on_hit_with_relation_tags(): void
    {
        Config::set('modularous.cache.use_tags', true);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $id = 77;
        $locale = 'en';
        app()->setLocale($locale);

        $cachedHtml = '<!DOCTYPE html><html><head></head><body>warmup-live-parity</body></html>';
        $item = new Page;
        $item->id = $id;
        $viewName = 'cms::page.custom';

        $key = CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            $locale,
        );

        ModularousCache::putWithRelations(
            $key,
            $cachedHtml,
            3600,
            $moduleName,
            $routeName,
            [Page::class => $id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $result = CmsPublicPresentationItemCache::rememberPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
        );

        $this->assertSame($cachedHtml, $result);
    }

    /** @test */
    public function remember_public_presentation_returns_cached_document_on_hit_without_rebuilding(): void
    {
        Config::set('modularous.cache.use_tags', true);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $id = 88;
        $locale = 'en';

        $cachedHtml = '<!DOCTYPE html><html><head></head><body>already-warm</body></html>';
        $item = new Page;
        $item->id = $id;
        $viewName = 'cms::page.custom';

        ModularousCache::putWithRelations(
            CmsPublicPresentationItemCache::cacheKeyForPublicPresentation(
                $moduleName,
                $routeName,
                $item,
                $viewName,
                $locale,
            ),
            $cachedHtml,
            3600,
            $moduleName,
            $routeName,
            [Page::class => $id],
            CmsPublicPresentationItemCache::CACHE_TYPE,
        );

        $result = CmsPublicPresentationItemCache::rememberPublicPresentation(
            $moduleName,
            $routeName,
            $item,
            $viewName,
            ['item' => $item],
            $locale,
        );

        $this->assertSame($cachedHtml, $result);
    }

    /** @test */
    public function it_generates_presentation_item_cache_key_with_module_route_id_and_locale(): void
    {
        $key = CmsPublicPresentationItemCache::cacheKey('PrimaryPage', 'Home', 42, 'en');

        $this->assertStringStartsWith('modularous:', $key);
        $this->assertStringContainsString('PrimaryPage', $key);
        $this->assertStringContainsString('Home', $key);
        $this->assertStringContainsString('presentationItem:42', $key);
    }

    /** @test */
    public function it_reports_presentation_item_enabled_from_config(): void
    {
        $this->assertTrue(CmsPublicPresentationItemCache::isEnabled('PrimaryPage', 'Home'));
        $this->assertFalse(CmsPublicPresentationItemCache::isEnabled('PrimaryPage', 'AboutUs'));
    }

    /** @test */
    public function modularous_cache_service_supports_presentation_item_ttl(): void
    {
        Config::set('modularous.cache.ttl.presentationItem', 777);
        Config::set('modularous.cache.modules.PrimaryPage.ttl.presentationItem', 888);

        $service = new ModularousCacheService;

        $this->assertEquals(888, $service->getTtl('presentationItem', 'PrimaryPage', 'Home'));
        $this->assertEquals(777, $service->getTtl('presentationItem', 'OtherModule'));
    }

    /** @test */
    public function modularous_cache_service_checks_presentation_item_type_toggle(): void
    {
        $service = new ModularousCacheService;

        $this->assertTrue($service->isEnabled('PrimaryPage', 'Home', 'presentationItem'));
        $this->assertFalse($service->isEnabled('PrimaryPage', 'Home', 'formItem'));
    }

    /** @test */
    public function wrapped_document_cache_key_matches_standard_presentation_item_key(): void
    {
        $wrappedKey = CmsPublicPresentationItemCache::cacheKey('PrimaryPage', 'Home', 42, 'en');
        $fullViewKey = CmsPublicPresentationItemCache::cacheKey('PrimaryPage', 'Home', 42, 'en', ['full' => true]);

        $this->assertStringContainsString('presentationItem:42', $wrappedKey);
        $this->assertNotSame($wrappedKey, $fullViewKey);
    }

    /** @test */
    public function remember_wrapped_document_html_returns_cached_full_document_on_hit(): void
    {
        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $id = 55;
        $locale = 'en';
        app()->setLocale($locale);

        $cachedHtml = '<!DOCTYPE html><html><head></head><body>cached-full-document</body></html>';
        $key = CmsPublicPresentationItemCache::cacheKey($moduleName, $routeName, $id, $locale);

        ModularousCache::putWithRelations(
            $key,
            $cachedHtml,
            3600,
            $moduleName,
            $routeName,
            [Page::class => $id],
        );

        $item = new Page;
        $item->id = $id;

        $result = CmsPublicPresentationItemCache::rememberWrappedDocumentHtml(
            $moduleName,
            $routeName,
            $item,
            'cms::page.custom',
            ['item' => $item],
        );

        $this->assertSame($cachedHtml, $result);
    }

    /** @test */
    public function remember_wrapped_document_html_returns_cached_full_document_on_hit_with_relation_tags(): void
    {
        Config::set('modularous.cache.use_tags', true);

        $moduleName = 'PrimaryPage';
        $routeName = 'Home';
        $id = 55;
        $locale = 'en';
        app()->setLocale($locale);

        $cachedHtml = '<!DOCTYPE html><html><head></head><body>cached-full-document</body></html>';
        $key = CmsPublicPresentationItemCache::cacheKey($moduleName, $routeName, $id, $locale);

        ModularousCache::putWithRelations(
            $key,
            $cachedHtml,
            3600,
            $moduleName,
            $routeName,
            [Page::class => $id],
        );

        $item = new Page;
        $item->id = $id;

        $result = CmsPublicPresentationItemCache::rememberWrappedDocumentHtml(
            $moduleName,
            $routeName,
            $item,
            'cms::page.custom',
            ['item' => $item],
        );

        $this->assertSame($cachedHtml, $result);
    }
}
