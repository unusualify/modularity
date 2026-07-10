<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKey;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKeyResolver;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Tests\TestCase;

class PresentationUrlCacheKeyResolverTest extends TestCase
{
    /** @test */
    public function it_resolves_path_only_lookup_key_from_module_route_config(): void
    {
        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $cacheService->expects($this->once())
            ->method('getPresentationCacheKeyStrategy')
            ->with('Blog', 'BlogLanding')
            ->willReturn(PresentationUrlCacheKey::STRATEGY_PATH_ONLY);

        $cacheService->expects($this->once())
            ->method('getPresentationCacheQueryAllowlist')
            ->with('Blog', 'BlogLanding')
            ->willReturn([]);

        $urlStore->expects($this->once())
            ->method('composeLookupKey')
            ->with('/blog', '')
            ->willReturn('/blog');

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);
        $request = Request::create('/blog?utm_source=newsletter', 'GET');

        $this->assertSame('/blog', $resolver->resolve($request, '/blog', 'Blog', 'BlogLanding'));
    }

    /** @test */
    public function it_bypasses_cache_when_allowlisted_route_receives_unknown_query_params(): void
    {
        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $cacheService->method('getPresentationCacheKeyStrategy')
            ->willReturn(PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST);

        $cacheService->method('getPresentationCacheQueryAllowlist')
            ->willReturn(['page', 'searchblogtext']);

        $urlStore->expects($this->never())->method('composeLookupKey');

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);
        $request = Request::create('/blog/search', 'GET', [
            'page' => '2',
            'utm_source' => 'newsletter',
        ]);

        $this->assertNull($resolver->resolve($request, '/blog/search', 'Blog', 'BlogLanding'));
    }

    /** @test */
    public function it_resolves_allowlisted_query_suffix_into_lookup_key(): void
    {
        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $cacheService->method('getPresentationCacheKeyStrategy')
            ->willReturn(PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST);

        $cacheService->method('getPresentationCacheQueryAllowlist')
            ->willReturn(['page', 'searchblogtext']);

        $urlStore->expects($this->once())
            ->method('composeLookupKey')
            ->with('/blog/search', 'page=2&searchblogtext=press')
            ->willReturn('/blog/search?page=2&searchblogtext=press');

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);
        $request = Request::create('/blog/search', 'GET', [
            'page' => '2',
            'searchblogtext' => 'press',
        ]);

        $this->assertSame(
            '/blog/search?page=2&searchblogtext=press',
            $resolver->resolve($request, '/blog/search', 'Blog', 'BlogLanding'),
        );
    }

    /** @test */
    public function it_reads_strategy_and_allowlist_from_module_route_config(): void
    {
        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $cacheService->expects($this->once())
            ->method('getPresentationCacheKeyStrategy')
            ->with('Blog', 'BlogLanding')
            ->willReturn(PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST);

        $cacheService->expects($this->once())
            ->method('getPresentationCacheQueryAllowlist')
            ->with('Blog', 'BlogLanding')
            ->willReturn(['page']);

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);

        [$strategy, $allowlist] = $resolver->resolveStrategyAndAllowlist('/blog', 'Blog', 'BlogLanding');

        $this->assertSame(PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST, $strategy);
        $this->assertSame(['page'], $allowlist);
    }

    /** @test */
    public function it_reads_strategy_and_allowlist_from_path_query_config_when_module_is_unknown(): void
    {
        Config::set('modularous.cache.presentationItem.url.path_query', [
            '/blog/search' => ['page', 'searchblogtext'],
        ]);

        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $urlStore->expects($this->once())
            ->method('normalizePath')
            ->with('/blog/search')
            ->willReturn('/blog/search');

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);

        [$strategy, $allowlist] = $resolver->resolveStrategyAndAllowlist('/blog/search', null, null);

        $this->assertSame(PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST, $strategy);
        $this->assertSame(['page', 'searchblogtext'], $allowlist);
    }

    /** @test */
    public function it_defaults_to_path_only_when_no_module_or_path_config_matches(): void
    {
        Config::set('modularous.cache.presentationItem.url.path_query', []);

        $cacheService = $this->createMock(ModularousCacheService::class);
        $urlStore = $this->createMock(UrlPresentationCacheStoreInterface::class);

        $urlStore->expects($this->once())
            ->method('normalizePath')
            ->with('/pages/about')
            ->willReturn('/pages/about');

        $resolver = new PresentationUrlCacheKeyResolver($cacheService, $urlStore);

        [$strategy, $allowlist] = $resolver->resolveStrategyAndAllowlist('/pages/about', null, null);

        $this->assertSame(PresentationUrlCacheKey::STRATEGY_PATH_ONLY, $strategy);
        $this->assertSame([], $allowlist);
    }
}
