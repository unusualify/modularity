<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Modules\Cms\Providers\CmsRouteServiceProvider;
use Modules\Cms\Providers\CmsServiceProvider;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\StalePublicationMeta;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicFrontUrlStaleResilienceTest extends TestCase
{
    private string $urlStalePath;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPublicFrontController::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-url-resilience-' . uniqid('', true);

        Config::set('modularous.cms_features.enabled', true);
        Config::set('modularous.cms_features.register_middlewares', true);
        Config::set('modularous.cms_routing.public_pages_enabled', true);
        Config::set('modularous.cms_routing.universal_cms_public_front', true);
        Config::set('modularous.cms_routing.auto_register_public_front', true);
        Config::set('modularous.cms_routing.public_front_routes_allow_any_host', true);
        Config::set('modularous.cms_routing.public_front_route_group_mode', 'locale_param');
        Config::set('modularous.cms_routing.localization_driver', 'translatable');
        Config::set('modularous.cms_routing.path_segment_locales', ['en', 'tr']);
        Config::set('modularous.cms_routing.default_locale', 'en');
        Config::set('modularous.cms_routing.resync_registry_after_parent_segments_change', false);
        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.presentationItem.serve_first', true);
        Config::set('modularous.cache.presentationItem.url.base_path', $this->urlStalePath);
        Config::set('translatable.locales', ['en', 'tr']);

        $this->app->forgetInstance('modularous.cache');
        $this->app->register(CmsServiceProvider::class);

        Config::set('database.connections.testdb.database', '/definitely/not/a/database.sqlite');
        DB::purge('testdb');

        $this->app->register(CmsRouteServiceProvider::class);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        parent::tearDown();
    }

    /** @test */
    public function it_serves_url_stale_through_public_front_stack_when_database_is_unavailable(): void
    {
        $this->seedUrlStale('en', '/pages/resilience', '<!DOCTYPE html><body>db-down-stack</body>');
        $this->breakDatabaseConnection();

        $response = $this->get('/en/pages/resilience');

        $response->assertOk();
        $response->assertSee('db-down-stack', false);
        $response->assertHeader(
            'X-Modularous-Cache',
            CmsPublicPresentationItemCache::cacheHeaderValue(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT),
        );
    }

    /** @test */
    public function it_serves_url_stale_on_non_public_front_host_before_routing(): void
    {
        Config::set('app.url', 'http://frontend.b2press.test');
        Config::set('modularous.cms_routing.public_front_route_domain', null);
        Config::set('modularous.cms_routing.public_front_routes_allow_any_host', false);

        $this->seedUrlStale('tr', '/ulke-pr-paketleri/arjantin', '<!DOCTYPE html><body>cms-host-stale</body>');

        $response = $this->withServerVariables(['HTTP_HOST' => 'cms.b2press.test'])
            ->get('/tr/ulke-pr-paketleri/arjantin');

        $response->assertOk();
        $response->assertSee('cms-host-stale', false);
        $response->assertHeader(
            'X-Modularous-Cache',
            CmsPublicPresentationItemCache::cacheHeaderValue(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT),
        );
    }

    /** @test */
    public function it_patches_stale_route_cache_missing_url_stale_middleware(): void
    {
        Config::set('modularous.cache.presentationItem.serve_first', false);
        $this->app->forgetInstance('modularous.cache');

        $route = Route::middleware(['web', 'modules.cms.visitor.redirect'])
            ->get('/stale-patch/{path}', CmsPublicFrontController::class)
            ->where('path', '.*');

        $this->assertNotContains('modules.cms.url_stale.serve', $route->middleware());

        CmsFrontRouteRegistrar::syncUrlStaleServeMiddlewareOnRegisteredPublicFrontRoutes();

        $middleware = $route->middleware();
        $this->assertContains('modules.cms.url_stale.serve', $middleware);
        $webIndex = array_search('web', $middleware, true);
        $staleIndex = array_search('modules.cms.url_stale.serve', $middleware, true);
        $this->assertNotFalse($webIndex);
        $this->assertNotFalse($staleIndex);
        $this->assertSame($webIndex + 1, $staleIndex);
    }

    private function seedUrlStale(string $locale, string $path, string $html): void
    {
        ModularousCache::getUrlKeyedStaleCache()->put(
            $locale,
            $path,
            $html,
            [
                'published' => true,
                'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            ],
            900,
            3600,
        );
    }

    private function breakDatabaseConnection(): void
    {
        Config::set('database.connections.testdb.database', '/definitely/not/a/database.sqlite');
        DB::purge('testdb');
        DB::reconnect('testdb');

        $this->assertFalse(database_exists());
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
