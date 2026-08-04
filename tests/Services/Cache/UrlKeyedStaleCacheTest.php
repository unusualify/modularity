<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Tests\TestCase;

class UrlKeyedStaleCacheTest extends TestCase
{
    private UrlKeyedStaleCache $cache;

    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/modularous-url-stale-test-' . uniqid('', true);
        $this->cache = new UrlKeyedStaleCache($this->basePath, 3600);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);
        parent::tearDown();
    }

    /** @test */
    public function it_exposes_base_path(): void
    {
        $this->assertSame($this->basePath, $this->cache->basePath());
    }

    /** @test */
    public function it_stores_and_reads_html_by_locale_and_path(): void
    {
        $html = '<!DOCTYPE html><html><body>url-hit</body></html>';
        $meta = [
            'published' => true,
            'visibility_profile' => 'standard',
        ];

        $this->assertTrue($this->cache->put('en', '/pages/blog', $html, $meta, 900, 3600));

        $entry = $this->cache->get('en', '/pages/blog');
        $this->assertNotNull($entry);
        $this->assertSame($html, $entry['html']);
        $this->assertSame(UrlKeyedStaleCache::FRESHNESS_HIT, $entry['freshness']);
        $this->assertSame('en', $entry['meta']['locale']);
        $this->assertSame('/pages/blog', $entry['meta']['normalized_path']);
    }

    /** @test */
    public function it_isolates_locales(): void
    {
        $this->cache->put('en', '/pages/blog', '<html>en</html>', ['published' => true], 900, 3600);
        $this->cache->put('tr', '/pages/blog', '<html>tr</html>', ['published' => true], 900, 3600);

        $this->assertSame('<html>en</html>', $this->cache->get('en', '/pages/blog')['html'] ?? null);
        $this->assertSame('<html>tr</html>', $this->cache->get('tr', '/pages/blog')['html'] ?? null);
    }

    /** @test */
    public function it_forgets_url_stale_files(): void
    {
        $this->cache->put('en', '/pages/old', '<html>old</html>', ['published' => true], 900, 3600);

        $this->assertTrue($this->cache->forget('en', '/pages/old'));
        $this->assertNull($this->cache->get('en', '/pages/old'));
    }

    /** @test */
    public function it_returns_stale_freshness_after_fresh_ttl_expires(): void
    {
        $this->cache->put('en', '/pages/swr', '<html>swr</html>', ['published' => true], 1, 3600);

        sleep(2);

        $entry = $this->cache->get('en', '/pages/swr');
        $this->assertNotNull($entry);
        $this->assertSame(UrlKeyedStaleCache::FRESHNESS_STALE, $entry['freshness']);
        $this->assertSame('<html>swr</html>', $entry['html']);
    }

    /** @test */
    public function it_isolates_query_variants_for_the_same_path(): void
    {
        $this->cache->put('en', '/blog/search', '<html>default</html>', ['published' => true], 900, 3600);
        $this->cache->put(
            'en',
            '/blog/search?page=2&searchblogtext=press',
            '<html>page-two</html>',
            ['published' => true],
            900,
            3600,
        );

        $this->assertSame('<html>default</html>', $this->cache->get('en', '/blog/search')['html'] ?? null);
        $this->assertSame(
            '<html>page-two</html>',
            $this->cache->get('en', '/blog/search?page=2&searchblogtext=press')['html'] ?? null,
        );
    }

    /** @test */
    public function it_forgets_all_query_variants_for_a_path(): void
    {
        $this->cache->put('en', '/blog', '<html>page-one</html>', ['published' => true], 900, 3600);
        $this->cache->put('en', '/blog?page=2', '<html>page-two</html>', ['published' => true], 900, 3600);

        $this->assertSame(2, $this->cache->forgetPathVariants('en', '/blog'));
        $this->assertNull($this->cache->get('en', '/blog'));
        $this->assertNull($this->cache->get('en', '/blog?page=2'));
    }

    /** @test */
    public function it_forgets_all_query_variants_for_multiple_paths_by_relation(): void
    {
        $meta = [
            'urlable_type' => 'Modules\\Blog\\Entities\\BlogLanding',
            'urlable_id' => 228,
            'module' => 'Blog',
            'route' => 'BlogLanding',
            'published' => true,
        ];

        $this->cache->put('en', '/blog', '<html>listing</html>', $meta, 900, 3600);
        $this->cache->put('en', '/blog?page=2', '<html>listing-page-two</html>', $meta, 900, 3600);
        $this->cache->put('en', '/blog/search', '<html>search</html>', $meta, 900, 3600);
        $this->cache->put(
            'en',
            '/blog/search?searchblogtext=press&page=2',
            '<html>search-page-two</html>',
            $meta,
            900,
            3600,
        );

        $this->assertSame(4, $this->cache->forgetByRelation('Modules\\Blog\\Entities\\BlogLanding', 228));
        $this->assertNull($this->cache->get('en', '/blog'));
        $this->assertNull($this->cache->get('en', '/blog?page=2'));
        $this->assertNull($this->cache->get('en', '/blog/search'));
        $this->assertNull($this->cache->get('en', '/blog/search?searchblogtext=press&page=2'));
    }

    /** @test */
    public function it_forgets_all_entries_for_a_module_route(): void
    {
        $landingMeta = [
            'urlable_type' => 'Modules\\Blog\\Entities\\BlogLanding',
            'urlable_id' => 228,
            'module' => 'Blog',
            'route' => 'BlogLanding',
            'published' => true,
        ];
        $postMeta = [
            'urlable_type' => 'Modules\\Blog\\Entities\\Blog',
            'urlable_id' => 99,
            'module' => 'Blog',
            'route' => 'Blog',
            'published' => true,
        ];

        $this->cache->put('en', '/blog', '<html>landing</html>', $landingMeta, 900, 3600);
        $this->cache->put('en', '/blog?page=2', '<html>landing-page-two</html>', $landingMeta, 900, 3600);
        $this->cache->put('en', '/blog/post-one', '<html>post</html>', $postMeta, 900, 3600);

        $this->assertSame(2, $this->cache->forgetByModuleRoute('Blog', 'BlogLanding'));
        $this->assertNull($this->cache->get('en', '/blog'));
        $this->assertNull($this->cache->get('en', '/blog?page=2'));
        $this->assertNotNull($this->cache->get('en', '/blog/post-one'));
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
