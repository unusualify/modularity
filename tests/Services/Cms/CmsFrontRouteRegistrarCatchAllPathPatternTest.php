<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Front\CmsPublicFrontController;
use Modules\Cms\Http\Controllers\Front\PublicSitemapController;
use Modules\Cms\Http\Controllers\Front\PublicSitemapXslController;
use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use Modules\Cms\Routing\CmsPublicSystemRouteRegistrar;
use Modules\Cms\Routing\CmsPublicSystemRoutes;
use ReflectionMethod;
use Unusualify\Modularous\Tests\TestCase;

final class CmsFrontRouteRegistrarCatchAllPathPatternTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', false);
        $this->app['config']->set('modularous.cms_stylesheets.public_route.enabled', false);
        $this->app['config']->set('modularous.cms_seo.robots.route_enabled', false);
        $this->app['config']->set('modularous.cms_sitemap.route_enabled', false);
        $this->app['config']->set('modularous.cms_routing.public_front_catch_all_exclude_path_prefixes', []);
    }

    public function test_signed_preview_prefix_is_blocked_from_path_param_when_enabled(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', true);
        $this->app['config']->set('modularous.cms_routing.signed_preview.path_prefix', 'cms/preview');

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertMatchesRegularExpression($re, 'pages/about');
        $this->assertMatchesRegularExpression($re, '');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/preview/Cms/Page/1/tr');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/preview');
        $this->assertMatchesRegularExpression($re, 'cms/previewx/other');
    }

    public function test_extra_exclude_prefix_from_config_blocks_path(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_catch_all_exclude_path_prefixes', ['internal/widget']);

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertMatchesRegularExpression($re, 'foo');
        $this->assertDoesNotMatchRegularExpression($re, 'internal/widget/run');
        $this->assertDoesNotMatchRegularExpression($re, 'internal/widget');
    }

    public function test_stylesheet_public_path_prefix_blocks_when_route_enabled(): void
    {
        $this->app['config']->set('modularous.cms_stylesheets.public_route.enabled', true);
        $this->app['config']->set('modularous.cms_stylesheets.public_route.path_prefix', 'cms/stylesheets');

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertDoesNotMatchRegularExpression($re, 'cms/stylesheets/demo.css');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/stylesheets');
        $this->assertMatchesRegularExpression($re, 'pages/about');
    }

    public function test_sitemap_and_robots_prefixes_blocked_when_routes_enabled(): void
    {
        $this->app['config']->set('modularous.cms_sitemap.route_enabled', true);
        $this->app['config']->set('modularous.cms_seo.robots.route_enabled', true);

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertDoesNotMatchRegularExpression($re, 'sitemap.xml');
        $this->assertDoesNotMatchRegularExpression($re, 'sitemap.xsl');
        $this->assertDoesNotMatchRegularExpression($re, 'robots.txt');
        $this->assertMatchesRegularExpression($re, 'pages/about');
    }

    public function test_reserved_path_prefixes_merges_catalog_and_config(): void
    {
        $this->app['config']->set('modularous.cms_sitemap.route_enabled', true);
        $this->app['config']->set('modularous.cms_routing.public_front_catch_all_exclude_path_prefixes', ['health']);

        $prefixes = CmsPublicSystemRoutes::reservedPathPrefixes();

        $this->assertContains('sitemap.xml', $prefixes);
        $this->assertContains('sitemap.xsl', $prefixes);
        $this->assertContains('health', $prefixes);
        $this->assertNotContains('robots.txt', $prefixes);
    }

    public function test_sitemap_registers_on_public_front_route_domain_host_not_full_url(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.b2press.test');
        $this->app['config']->set('modularous.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularous.cms_routing.public_front_routes_allow_any_host', false);
        $this->app['config']->set('modularous.cms_routing.bind_public_routes_to_app_url_host', null);
        $this->app['config']->set('modularous.cms_features.enabled', true);
        $this->app['config']->set('modularous.cms_sitemap.route_enabled', true);
        $this->app['config']->set('modularous.cms_seo.robots.route_enabled', false);
        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', false);
        $this->app['config']->set('modularous.cms_stylesheets.public_route.enabled', false);

        CmsPublicSystemRouteRegistrar::registerAll();
        Route::getRoutes()->refreshNameLookups();

        $route = Route::getRoutes()->getByName('cms.sitemap');
        $this->assertNotNull($route);
        $this->assertSame('frontend.b2press.test', $route->getDomain());
        $uses = $route->getAction('uses') ?? $route->getAction('controller');
        $class = is_string($uses) ? (str_contains($uses, '@') ? mb_strstr($uses, '@', true) : $uses) : null;
        $this->assertSame(PublicSitemapController::class, $class);

        $xslRoute = Route::getRoutes()->getByName('cms.sitemap_xsl');
        $this->assertNotNull($xslRoute);
        $this->assertSame('frontend.b2press.test', $xslRoute->getDomain());
        $xslUses = $xslRoute->getAction('uses') ?? $xslRoute->getAction('controller');
        $xslClass = is_string($xslUses) ? (str_contains($xslUses, '@') ? mb_strstr($xslUses, '@', true) : $xslUses) : null;
        $this->assertSame(PublicSitemapXslController::class, $xslClass);
    }

    public function test_host_specific_route_registered_before_catch_all_wins_match(): void
    {
        $domain = 'frontend.b2press.test';

        Route::domain($domain)->get('test', static fn () => response('host-wins'))->name('host.test');

        Route::domain($domain)->get('{path}', CmsPublicFrontController::class)
            ->where('path', '.*')
            ->name('cms.page');

        $request = Request::create('http://frontend.b2press.test/test', 'GET');
        $matched = Route::getRoutes()->match($request);

        $this->assertSame('host.test', $matched->getName());
    }

    /** @return non-empty-string */
    private static function reflectCatchAllPattern(): string
    {
        $m = new ReflectionMethod(CmsFrontRouteRegistrar::class, 'catchAllPathParameterPattern');
        $m->setAccessible(true);

        return (string) $m->invoke(null);
    }
}
