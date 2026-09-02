<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Contracts\CmsVisitorRequestContextResolverInterface;
use Modules\Cms\Http\Middleware\ServeUrlKeyedStaleMiddleware;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsVisitorRedirectResolver;
use Modules\Cms\Support\CmsPublicPresentationItemCache;
use Modules\Cms\Support\StalePublicationMeta;
use Unusualify\Modularous\Contracts\ModulePresentationAssetLoaderInterface;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Tests\TestCase;

class ServeUrlKeyedStaleMiddlewareTest extends TestCase
{
    private string $urlStalePath;

    private function makeVisitorResolver(): CmsVisitorRedirectResolver
    {
        $canonical = new CanonicalUrlResolver;

        return new CmsVisitorRedirectResolver($canonical, new TranslatableCmsLocalizationAdapter($canonical));
    }

    private function makeMiddleware(
        CmsVisitorRequestContextResolverInterface $resolver,
        CmsLocalizationContract $localization,
    ): ServeUrlKeyedStaleMiddleware {
        return new ServeUrlKeyedStaleMiddleware(
            $resolver,
            $localization,
            $this->app->make(ModulePresentationAssetLoaderInterface::class),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(ServeUrlKeyedStaleMiddleware::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        $this->urlStalePath = sys_get_temp_dir() . '/modularous-url-mw-test-' . uniqid('', true);

        Config::set('modularous.cache.presentationItem.store', 'url');
        Config::set('modularous.cache.presentationItem.serve_first', true);
        Config::set('modularous.cache.presentationItem.url.base_path', $this->urlStalePath);
        Config::set('translatable.locales', ['en', 'tr']);
        Config::set('modularous.cms_routing.default_locale', 'en');

        $this->app->forgetInstance('modularous.cache');
        $this->configurePresentationCacheRoute('Cms', 'Page');
    }

    private function configurePresentationCacheRoute(string $module, string $route, bool $enabled = true): void
    {
        Config::set('modularous.cache.enabled', true);
        Config::set('modularous.cache.modules.' . $module . '.enabled', true);
        Config::set('modularous.cache.modules.' . $module . '.routes.' . $route . '.enabled', true);
        Config::set('modularous.cache.modules.' . $module . '.routes.' . $route . '.types.presentationItem', $enabled);
        $this->app->forgetInstance('modularous.cache');
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function cacheMeta(array $extra = []): array
    {
        return array_merge([
            'published' => true,
            'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            'module' => 'Cms',
            'route' => 'Page',
        ], $extra);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->urlStalePath);
        parent::tearDown();
    }

    /** @test */
    public function it_serves_url_stale_html_without_hitting_controller(): void
    {
        $html = '<!DOCTYPE html><html><body>middleware-hit</body></html>';
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/pages/blog',
            $html,
            $this->cacheMeta(),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/pages/blog', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/pages/blog', 'GET');

        $response = $middleware->handle($request, fn () => abort(500, 'controller-should-not-run'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($html, $response->getContent());
        $this->assertSame(
            CmsPublicPresentationItemCache::cacheHeaderValue(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT),
            $response->headers->get('X-Modularous-Cache'),
        );
    }

    /** @test */
    public function it_serves_url_stale_html_when_database_is_unavailable(): void
    {
        $html = '<!DOCTYPE html><html><body>db-down-hit</body></html>';
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/pages/blog',
            $html,
            $this->cacheMeta(),
            900,
            3600,
        );

        Config::set('database.connections.testdb.database', '/definitely/not/a/database.sqlite');
        DB::purge('testdb');

        $this->assertFalse(database_exists());

        $resolver = $this->makeVisitorResolver();
        $localization = new TranslatableCmsLocalizationAdapter(new CanonicalUrlResolver);

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/pages/blog', 'GET');

        $response = $middleware->handle($request, fn () => abort(500, 'controller-should-not-run'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($html, $response->getContent());
        $this->assertSame(
            CmsPublicPresentationItemCache::cacheHeaderValue(CmsPublicPresentationItemCache::CACHE_STATUS_URL_HIT),
            $response->headers->get('X-Modularous-Cache'),
        );
    }

    /** @test */
    public function visitor_redirect_skips_database_lookups_when_url_stale_resilience_is_active(): void
    {
        Config::set('database.connections.testdb.database', '/definitely/not/a/database.sqlite');
        DB::purge('testdb');

        $this->assertFalse(database_exists());

        $resolver = $this->makeVisitorResolver();
        $request = Request::create('/en/some-old-path', 'GET');

        $this->assertNull($resolver->resolveRedirectResponse($request));
    }

    /** @test */
    public function it_serves_query_variant_from_url_stale_cache(): void
    {
        Config::set('modularous.cache.presentationItem.url.path_query', [
            '/blog/search' => ['page', 'searchblogtext'],
        ]);
        $this->app->forgetInstance('modularous.cache');

        $this->configurePresentationCacheRoute('Blog', 'BlogLanding');

        $html = '<!DOCTYPE html><html><body>search-page-2</body></html>';
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/blog/search?page=2&searchblogtext=press',
            $html,
            $this->cacheMeta([
                'module' => 'Blog',
                'route' => 'BlogLanding',
            ]),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/blog/search', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/blog/search?page=2&searchblogtext=press', 'GET');

        $response = $middleware->handle($request, fn () => abort(500, 'controller-should-not-run'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($html, $response->getContent());
    }

    /** @test */
    public function it_bypasses_cache_for_disallowed_query_params(): void
    {
        Config::set('modularous.cache.presentationItem.url.path_query', [
            '/blog/search' => ['page', 'searchblogtext'],
        ]);
        $this->app->forgetInstance('modularous.cache');

        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/blog/search',
            '<html>default</html>',
            $this->cacheMeta([
                'module' => 'Blog',
                'route' => 'BlogLanding',
            ]),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/blog/search', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/blog/search?utm_source=newsletter', 'GET');

        $response = $middleware->handle($request, fn () => response('controller', 200));

        $this->assertSame('controller', $response->getContent());
    }

    /** @test */
    public function it_does_not_serve_stale_html_on_panel_urls(): void
    {
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/admin/dashboard',
            '<html>should-not-serve</html>',
            $this->cacheMeta(),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/admin/dashboard', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('http://localhost/admin/dashboard', 'GET');

        $response = $middleware->handle($request, fn () => response('panel', 200));

        $this->assertSame('panel', $response->getContent());
    }

    /** @test */
    public function it_falls_through_when_meta_is_not_visible(): void
    {
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/pages/hidden',
            '<html>hidden</html>',
            $this->cacheMeta([
                'published' => false,
            ]),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/pages/hidden', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/pages/hidden', 'GET');

        $response = $middleware->handle($request, fn () => response('controller', 200));

        $this->assertSame('controller', $response->getContent());
    }

    /** @test */
    public function it_falls_through_when_presentation_item_cache_is_disabled_for_route(): void
    {
        $this->configurePresentationCacheRoute('PrimaryPage', 'SubmitPressRelease', false);

        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/submit-press-release',
            '<html>cached-submit</html>',
            $this->cacheMeta([
                'module' => 'PrimaryPage',
                'route' => 'SubmitPressRelease',
            ]),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/submit-press-release', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/submit-press-release', 'GET');

        $response = $middleware->handle($request, fn () => response('controller', 200));

        $this->assertSame('controller', $response->getContent());
    }

    /** @test */
    public function it_falls_through_when_cache_meta_is_missing_module_or_route(): void
    {
        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/pages/orphan',
            '<html>orphan</html>',
            [
                'published' => true,
                'visibility_profile' => StalePublicationMeta::PROFILE_STANDARD,
            ],
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/pages/orphan', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/pages/orphan', 'GET');

        $response = $middleware->handle($request, fn () => response('controller', 200));

        $this->assertSame('controller', $response->getContent());
    }

    /** @test */
    public function it_refreshes_csrf_tokens_when_serving_cached_html(): void
    {
        $session = app('session.store');
        $session->start();

        $freshToken = csrf_token();
        $this->assertNotSame('', $freshToken);

        $html = '<head><meta name="csrf-token" content="stale-meta"></head>'
            . '<form><input type="hidden" name="_token" value="stale-input" autocomplete="off"></form>';

        ModularousCache::getUrlKeyedStaleCache()->put(
            'en',
            '/pages/blog',
            $html,
            $this->cacheMeta(),
            900,
            3600,
        );

        $resolver = new class implements CmsVisitorRequestContextResolverInterface
        {
            public function shouldExcludeRequest(Request $request): bool
            {
                return false;
            }

            public function resolveLocalePathKeyAndExplicitFlag(Request $request): array
            {
                return ['en', '/pages/blog', true];
            }
        };

        $localization = $this->createMock(CmsLocalizationContract::class);
        $localization->method('defaultLocale')->willReturn('en');

        $middleware = $this->makeMiddleware($resolver, $localization);
        $request = Request::create('/en/pages/blog', 'GET');
        $request->setLaravelSession($session);

        $response = $middleware->handle($request, fn () => abort(500, 'controller-should-not-run'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<meta name="csrf-token" content="' . $freshToken . '">', $response->getContent());
        $this->assertStringNotContainsString('stale-meta', $response->getContent());
        $this->assertStringNotContainsString('stale-input', $response->getContent());
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
