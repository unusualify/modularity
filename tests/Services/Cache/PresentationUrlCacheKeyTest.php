<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Illuminate\Http\Request;
use Unusualify\Modularous\Services\Cache\PresentationUrlCacheKey;
use Unusualify\Modularous\Tests\TestCase;

class PresentationUrlCacheKeyTest extends TestCase
{
    /** @test */
    public function it_builds_sorted_query_suffix_from_allowlist(): void
    {
        $suffix = PresentationUrlCacheKey::normalizeQuerySuffix(
            ['searchblogtext' => 'press', 'page' => '2', 'ignored' => 'x'],
            ['page', 'searchblogtext'],
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
        );

        $this->assertSame('page=2&searchblogtext=press', $suffix);
    }

    /** @test */
    public function it_omits_default_page_one_from_query_suffix(): void
    {
        $suffix = PresentationUrlCacheKey::normalizeQuerySuffix(
            ['page' => '1', 'searchblogtext' => 'news'],
            ['page', 'searchblogtext'],
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
        );

        $this->assertSame('searchblogtext=news', $suffix);
    }

    /** @test */
    public function it_detects_disallowed_query_params_for_allowlist_strategy(): void
    {
        $request = Request::create('/blog/search', 'GET', [
            'page' => '2',
            'utm_source' => 'newsletter',
        ]);

        $this->assertTrue(PresentationUrlCacheKey::shouldBypassRequest(
            $request,
            ['page', 'searchblogtext'],
            PresentationUrlCacheKey::STRATEGY_PATH_AND_QUERY_ALLOWLIST,
        ));
    }

    /** @test */
    public function it_ignores_query_params_for_path_only_strategy(): void
    {
        $request = Request::create('/pages/about', 'GET', ['utm_source' => 'newsletter']);

        $this->assertFalse(PresentationUrlCacheKey::shouldBypassRequest(
            $request,
            [],
            PresentationUrlCacheKey::STRATEGY_PATH_ONLY,
        ));

        $this->assertSame('', PresentationUrlCacheKey::normalizeQuerySuffix(
            $request->query->all(),
            [],
            PresentationUrlCacheKey::STRATEGY_PATH_ONLY,
        ));
    }

    /** @test */
    public function it_composes_lookup_key_with_query_suffix(): void
    {
        $this->assertSame(
            '/blog/search?page=2&searchblogtext=press',
            PresentationUrlCacheKey::composeLookupKey('/blog/search', 'page=2&searchblogtext=press'),
        );
    }
}
