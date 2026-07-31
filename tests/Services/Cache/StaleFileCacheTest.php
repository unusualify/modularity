<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cache;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Tests\TestCase;

class StaleFileCacheTest extends TestCase
{
    private StaleFileCache $cache;

    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/modularous-stale-test-' . uniqid('', true);
        $this->cache = new StaleFileCache($this->basePath, 3600);
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
    public function it_stores_and_reads_stale_html_by_cache_key(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:42:' . md5(serialize(['locale' => 'en']));
        $html = '<!DOCTYPE html><html><body>stale</body></html>';

        $this->assertTrue($this->cache->put($key, $html, 3600, [\stdClass::class => 42]));
        $this->assertSame($html, $this->cache->get($key));
    }

    /** @test */
    public function it_reads_stale_without_redis(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:7:' . md5(serialize(['locale' => 'tr']));
        $this->cache->put($key, '<html>offline</html>', 3600, ['Modules\\Cms\\Entities\\Page' => 7]);

        $this->assertSame('<html>offline</html>', $this->cache->get($key));
        $this->assertNull($this->cache->get('missing-key', null));
    }

    /** @test */
    public function it_forgets_stale_file_on_explicit_purge(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:9:' . md5(serialize(['locale' => 'en']));
        $this->cache->put($key, '<html>purge-me</html>', 3600, ['Page' => 9]);

        $this->assertTrue($this->cache->forget($key));
        $this->assertNull($this->cache->get($key));
    }

    /** @test */
    public function it_forgets_stale_files_by_relation(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:11:' . md5(serialize(['locale' => 'en']));
        $this->cache->put($key, '<html>relation</html>', 3600, ['Modules\\Cms\\Entities\\Page' => 11]);

        $deleted = $this->cache->forgetByRelation('Modules\\Cms\\Entities\\Page', 11);

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertNull($this->cache->get($key));
    }

    /** @test */
    public function it_forgets_stale_files_by_module_route_and_id(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:15:' . md5(serialize(['locale' => 'en', 'full' => true]));
        $this->cache->put($key, '<html>route-id</html>', 3600, ['Page' => 15]);

        $deleted = $this->cache->forgetByModuleRouteId('PrimaryPage', 'Home', 15);

        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertNull($this->cache->get($key));
    }

    /** @test */
    public function it_forgets_stale_files_by_module_route(): void
    {
        $keyOne = 'modularous:PrimaryPage:Home:presentationItem:15:' . md5(serialize(['locale' => 'en']));
        $keyTwo = 'modularous:PrimaryPage:Home:presentationItem:16:' . md5(serialize(['locale' => 'tr']));
        $this->cache->put($keyOne, '<html>one</html>', 3600, ['Page' => 15]);
        $this->cache->put($keyTwo, '<html>two</html>', 3600, ['Page' => 16]);

        $deleted = $this->cache->forgetByModuleRoute('PrimaryPage', 'Home');

        $this->assertGreaterThanOrEqual(2, $deleted);
        $this->assertNull($this->cache->get($keyOne));
        $this->assertNull($this->cache->get($keyTwo));
    }

    /** @test */
    public function it_reads_stale_using_relation_path_first(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:42:' . md5(serialize(['locale' => 'en']));
        $html = '<html>relation-path</html>';

        $this->cache->put($key, $html, 3600, [\stdClass::class => 42]);

        $this->assertSame($html, $this->cache->get($key, null, [\stdClass::class => 42]));
    }

    /** @test */
    public function it_expires_stale_files_after_ttl(): void
    {
        $cache = new StaleFileCache($this->basePath . '-ttl', 1);
        $key = 'modularous:PrimaryPage:Home:presentationItem:1:' . md5(serialize(['locale' => 'en']));

        $cache->put($key, '<html>ttl</html>', 1);
        sleep(2);

        $this->assertNull($cache->get($key));
    }

    /** @test */
    public function it_stores_en_and_tr_stale_files_in_separate_locale_directories(): void
    {
        Config::set('app.locales', ['en', 'tr']);

        $enKey = 'modularous:PrimaryPage:Home:presentationItem:42:' . md5(serialize(['locale' => 'en']));
        $trKey = 'modularous:PrimaryPage:Home:presentationItem:42:' . md5(serialize(['locale' => 'tr']));
        $relations = [\stdClass::class => 42];

        $this->assertTrue($this->cache->put($enKey, '<html>en</html>', 3600, array_merge($relations, [
            StaleFileCache::LOCALE_RELATION_KEY => 'en',
        ])));
        $this->assertTrue($this->cache->put($trKey, '<html>tr</html>', 3600, array_merge($relations, [
            StaleFileCache::LOCALE_RELATION_KEY => 'tr',
        ])));

        $modelSegment = class_basename(\stdClass::class);
        $enPath = $this->basePath . "/PrimaryPage/Home/{$modelSegment}/42/en/" . md5(serialize(['locale' => 'en'])) . '.html';
        $trPath = $this->basePath . "/PrimaryPage/Home/{$modelSegment}/42/tr/" . md5(serialize(['locale' => 'tr'])) . '.html';

        $this->assertFileExists($enPath);
        $this->assertFileExists($trPath);
        $this->assertSame('<html>en</html>', $this->cache->get($enKey, null, array_merge($relations, [
            StaleFileCache::LOCALE_RELATION_KEY => 'en',
        ])));
        $this->assertSame('<html>tr</html>', $this->cache->get($trKey, null, array_merge($relations, [
            StaleFileCache::LOCALE_RELATION_KEY => 'tr',
        ])));
    }

    /** @test */
    public function it_reads_legacy_stale_paths_without_locale_directory(): void
    {
        $hash = md5(serialize(['locale' => 'en']));
        $key = 'modularous:PrimaryPage:Home:presentationItem:99:' . $hash;
        $legacyPath = $this->basePath . '/PrimaryPage/Home/Page/99/' . $hash . '.html';

        $this->ensureDirectory(dirname($legacyPath));
        file_put_contents($legacyPath, '<html>legacy</html>');

        $this->assertSame('<html>legacy</html>', $this->cache->get($key, null, ['Page' => 99]));
    }

    /** @test */
    public function it_inspects_fresh_meta_by_module_route_id_without_deleting(): void
    {
        $key = 'modularous:PrimaryPage:Home:presentationItem:21:' . md5(serialize(['locale' => 'en']));
        $this->cache->put($key, '<html>inspect</html>', 3600, ['Page' => 21]);

        $inspected = $this->cache->inspectByModuleRouteId('PrimaryPage', 'Home', 21);

        $this->assertNotNull($inspected);
        $this->assertSame('HIT', $inspected['freshness']);
        $this->assertGreaterThan(time(), $inspected['expires_at']);
        $this->assertSame('<html>inspect</html>', $this->cache->get($key, null, ['Page' => 21]));
    }

    /** @test */
    public function it_inspects_expired_meta_by_module_route_id_without_deleting(): void
    {
        $hash = md5(serialize(['locale' => 'en']));
        $htmlPath = $this->basePath . '/PrimaryPage/Home/Page/22/en/' . $hash . '.html';
        $this->ensureDirectory(dirname($htmlPath));
        file_put_contents($htmlPath, '<html>expired</html>');
        file_put_contents($htmlPath . '.meta', (string) json_encode(['expires_at' => time() - 10]));

        $inspected = $this->cache->inspectByModuleRouteId('PrimaryPage', 'Home', 22);

        $this->assertNotNull($inspected);
        $this->assertSame('STALE', $inspected['freshness']);
        $this->assertFileExists($htmlPath);
        $this->assertFileExists($htmlPath . '.meta');
    }

    /** @test */
    public function it_returns_null_when_inspect_finds_no_meta(): void
    {
        $this->assertNull($this->cache->inspectByModuleRouteId('PrimaryPage', 'Home', 999));
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = scandir($directory) ?: [];
        foreach ($items as $item) {
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
