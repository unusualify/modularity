<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Unusualify\Modularous\Services\Cache\FileUrlPresentationCacheDriver;
use Unusualify\Modularous\Services\Cache\UrlKeyedStaleCache;
use Unusualify\Modularous\Tests\TestCase;

class FileUrlPresentationCacheDriverTest extends TestCase
{
    private FileUrlPresentationCacheDriver $driver;

    private UrlKeyedStaleCache $cache;

    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/modularous-url-driver-test-' . uniqid('', true);
        $this->cache = new UrlKeyedStaleCache($this->basePath, 3600);
        $this->driver = new FileUrlPresentationCacheDriver($this->cache);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);
        parent::tearDown();
    }

    /** @test */
    public function it_exposes_the_underlying_file_cache(): void
    {
        $this->assertSame($this->cache, $this->driver->underlyingFileCache());
    }

    /** @test */
    public function it_delegates_put_and_get_to_the_underlying_cache(): void
    {
        $meta = ['published' => true, 'module' => 'Blog', 'route' => 'BlogLanding'];

        $this->assertTrue($this->driver->put('en', '/blog', '<html>driver</html>', $meta, 900, 3600));

        $entry = $this->driver->get('en', '/blog');
        $this->assertNotNull($entry);
        $this->assertSame('<html>driver</html>', $entry['html']);
        $this->assertSame(UrlKeyedStaleCache::FRESHNESS_HIT, $entry['freshness']);
    }

    /** @test */
    public function it_delegates_forget_to_the_underlying_cache(): void
    {
        $this->cache->put('en', '/blog/old', '<html>old</html>', ['published' => true], 900, 3600);

        $this->assertTrue($this->driver->forget('en', '/blog/old'));
        $this->assertNull($this->driver->get('en', '/blog/old'));
    }

    /** @test */
    public function it_delegates_forget_path_variants_to_the_underlying_cache(): void
    {
        $meta = ['published' => true, 'module' => 'Blog', 'route' => 'BlogLanding'];
        $this->cache->put('en', '/blog', '<html>one</html>', $meta, 900, 3600);
        $this->cache->put('en', '/blog?page=2', '<html>two</html>', $meta, 900, 3600);

        $this->assertSame(2, $this->driver->forgetPathVariants('en', '/blog'));
        $this->assertNull($this->driver->get('en', '/blog'));
        $this->assertNull($this->driver->get('en', '/blog?page=2'));
    }

    /** @test */
    public function it_delegates_forget_by_relation_to_the_underlying_cache(): void
    {
        $meta = [
            'urlable_type' => 'Modules\\Blog\\Entities\\BlogLanding',
            'urlable_id' => 42,
            'module' => 'Blog',
            'route' => 'BlogLanding',
            'published' => true,
        ];

        $this->cache->put('en', '/blog', '<html>relation</html>', $meta, 900, 3600);

        $this->assertSame(1, $this->driver->forgetByRelation('Modules\\Blog\\Entities\\BlogLanding', 42));
        $this->assertNull($this->driver->get('en', '/blog'));
    }

    /** @test */
    public function it_delegates_forget_by_module_route_to_the_underlying_cache(): void
    {
        $meta = [
            'urlable_type' => 'Modules\\Blog\\Entities\\BlogLanding',
            'urlable_id' => 7,
            'module' => 'Blog',
            'route' => 'BlogLanding',
            'published' => true,
        ];

        $this->cache->put('en', '/blog', '<html>route</html>', $meta, 900, 3600);
        $this->cache->put('en', '/blog/post', '<html>other-route</html>', [
            'module' => 'Blog',
            'route' => 'Blog',
            'published' => true,
        ], 900, 3600);

        $this->assertSame(1, $this->driver->forgetByModuleRoute('Blog', 'BlogLanding'));
        $this->assertNull($this->driver->get('en', '/blog'));
        $this->assertNotNull($this->driver->get('en', '/blog/post'));
    }

    /** @test */
    public function it_delegates_lookup_key_helpers_to_the_underlying_cache(): void
    {
        $this->assertSame('/blog/search', $this->driver->normalizePath('blog/search'));
        $this->assertSame(
            '/blog/search?page=2',
            $this->driver->composeLookupKey('/blog/search', 'page=2'),
        );
        $this->assertSame(
            ['/blog/search', 'page=2'],
            $this->driver->splitLookupKey('/blog/search?page=2'),
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
