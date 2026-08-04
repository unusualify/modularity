<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Mockery;
use Unusualify\Modularous\Contracts\Cache\UrlPresentationCacheStoreInterface;
use Unusualify\Modularous\Entities\Traits\Core\HasCaching;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Services\Cache\StaleFileCache;
use Unusualify\Modularous\Tests\TestCase;

class HasCachingPresentationItemCacheTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/modularous-hascaching-test-' . uniqid('', true);
        Config::set('app.locales', ['en', 'tr']);
        Config::set('app.locale', 'en');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->basePath);
        Mockery::close();

        parent::tearDown();
    }

    public function test_presentation_item_cache_formatted_shows_missing_when_disabled(): void
    {
        ModularousCache::shouldReceive('isPresentationCacheEnabled')
            ->once()
            ->andReturn(false);

        $model = $this->makeModel(1);

        $html = $model->presentation_item_cache_formatted;

        $this->assertStringContainsString('<v-chip-group', $html);
        $this->assertStringContainsString('<v-tooltip', $html);
        $this->assertStringContainsString('>EN</v-chip>', $html);
        $this->assertStringContainsString('>TR</v-chip>', $html);
        $this->assertStringContainsString('text="Not cached"', $html);
        $this->assertStringNotContainsString('EN ·', $html);
        $this->assertSame(2, mb_substr_count($html, 'mdi-close-circle-outline'));
    }

    public function test_presentation_item_cache_formatted_shows_fresh_for_model_store_hit(): void
    {
        $cache = new StaleFileCache($this->basePath, 3600);
        $key = 'modularous:TestModule:TestRoute:presentationItem:5:' . md5(serialize(['locale' => 'en']));
        $cache->put($key, '<html>fresh</html>', 900, array_merge(['Page' => 5], [
            StaleFileCache::LOCALE_RELATION_KEY => 'en',
        ]));

        ModularousCache::shouldReceive('isPresentationCacheEnabled')->once()->andReturn(true);
        ModularousCache::shouldReceive('getPresentationCacheStore')->once()->andReturn('model');
        ModularousCache::shouldReceive('getStaleFileCache')->once()->andReturn($cache);
        ModularousCache::shouldReceive('getStaleTtl')->once()->with('presentationItem')->andReturn(604800);
        ModularousCache::shouldReceive('getTtl')->once()->with('presentationItem', 'TestModule', 'TestRoute')->andReturn(900);

        $model = $this->makeModel(5);
        $html = $model->presentation_item_cache_formatted;

        $labelDay = Carbon::createFromTimestamp(time() + 604800)
            ->timezone(config('app.timezone', 'UTC'))
            ->format('Y-m-d');

        $this->assertStringContainsString('color="success"', $html);
        $this->assertStringContainsString('>EN</v-chip>', $html);
        $this->assertStringContainsString('>TR</v-chip>', $html);
        $this->assertStringContainsString('mdi-check-circle', $html);
        $this->assertStringContainsString('mdi-close-circle-outline', $html);
        $this->assertStringContainsString('text="Valid until ' . $labelDay, $html);
        $this->assertStringContainsString('text="Not cached"', $html);
        $this->assertStringNotContainsString('EN ·', $html);
    }

    public function test_presentation_item_cache_formatted_shows_valid_when_past_fresh_but_within_stale_ttl(): void
    {
        $hash = md5(serialize(['locale' => 'en']));
        $expiresAt = time() - 60;
        $htmlPath = $this->basePath . '/TestModule/TestRoute/Page/6/en/' . $hash . '.html';
        if (! is_dir(dirname($htmlPath))) {
            mkdir(dirname($htmlPath), 0755, true);
        }
        file_put_contents($htmlPath, '<html>expired-fresh</html>');
        file_put_contents($htmlPath . '.meta', (string) json_encode([
            'expires_at' => $expiresAt,
            'locale' => 'en',
        ]));

        $cache = new StaleFileCache($this->basePath, 3600);

        ModularousCache::shouldReceive('isPresentationCacheEnabled')->once()->andReturn(true);
        ModularousCache::shouldReceive('getPresentationCacheStore')->once()->andReturn('model');
        ModularousCache::shouldReceive('getStaleFileCache')->once()->andReturn($cache);
        ModularousCache::shouldReceive('getStaleTtl')->once()->with('presentationItem')->andReturn(604800);
        ModularousCache::shouldReceive('getTtl')->once()->with('presentationItem', 'TestModule', 'TestRoute')->andReturn(900);

        $model = $this->makeModel(6);
        $html = $model->presentation_item_cache_formatted;

        $staleUntil = ($expiresAt - 900) + 604800;
        $validLabel = Carbon::createFromTimestamp($staleUntil)
            ->timezone(config('app.timezone', 'UTC'))
            ->format('Y-m-d H:i');

        $this->assertStringContainsString('>EN</v-chip>', $html);
        $this->assertStringContainsString('color="success"', $html);
        $this->assertStringContainsString('mdi-check-circle', $html);
        $this->assertStringContainsString('text="Valid until ' . $validLabel . '"', $html);
        $this->assertStringNotContainsString('Stale until', $html);
        $this->assertFileExists($htmlPath);
    }

    public function test_presentation_item_cache_formatted_renders_chip_per_url_locale(): void
    {
        $enExpires = time() + 604800;
        $trExpires = time() + 500000;
        $urlStore = Mockery::mock(UrlPresentationCacheStoreInterface::class);
        $urlStore->shouldReceive('get')
            ->once()
            ->with('en', '/hello')
            ->andReturn([
                'html' => '<html>en</html>',
                'meta' => [
                    'expires_at' => time() + 600,
                    'stale_expires_at' => $enExpires,
                ],
                'freshness' => UrlPresentationCacheStoreInterface::FRESHNESS_HIT,
            ]);
        $urlStore->shouldReceive('get')
            ->once()
            ->with('tr', '/merhaba')
            ->andReturn([
                'html' => '<html>tr</html>',
                'meta' => [
                    'expires_at' => time() - 10,
                    'stale_expires_at' => $trExpires,
                ],
                'freshness' => UrlPresentationCacheStoreInterface::FRESHNESS_STALE,
            ]);

        ModularousCache::shouldReceive('isPresentationCacheEnabled')->once()->andReturn(true);
        ModularousCache::shouldReceive('getPresentationCacheStore')->once()->andReturn('url');
        ModularousCache::shouldReceive('getUrlPresentationCacheStore')->once()->andReturn($urlStore);

        $model = new class extends Model
        {
            use HasCaching;

            protected $table = 'has_caching_presentation_test';

            public function getCacheModuleName()
            {
                return 'TestModule';
            }

            public function getCacheModuleRouteName()
            {
                return 'TestRoute';
            }

            protected function resolvePresentationItemLocalePaths(): array
            {
                return [
                    'en' => '/hello',
                    'tr' => '/merhaba',
                ];
            }
        };
        $model->setRawAttributes(['id' => 7]);
        $model->exists = true;

        $html = $model->presentation_item_cache_formatted;

        $enLabel = Carbon::createFromTimestamp($enExpires)
            ->timezone(config('app.timezone', 'UTC'))
            ->format('Y-m-d H:i');
        $trLabel = Carbon::createFromTimestamp($trExpires)
            ->timezone(config('app.timezone', 'UTC'))
            ->format('Y-m-d H:i');

        $this->assertStringContainsString('>EN</v-chip>', $html);
        $this->assertStringContainsString('>TR</v-chip>', $html);
        $this->assertSame(2, mb_substr_count($html, 'mdi-check-circle'));
        $this->assertStringNotContainsString('mdi-clock-alert-outline', $html);
        $this->assertStringContainsString('text="Valid until ' . $enLabel . '"', $html);
        $this->assertStringContainsString('text="Valid until ' . $trLabel . '"', $html);
        $this->assertStringNotContainsString('Stale until', $html);
        $this->assertStringNotContainsString('EN ·', $html);
        $this->assertStringNotContainsString('TR ·', $html);
    }

    protected function makeModel(int $id): Model
    {
        $model = new class extends Model
        {
            use HasCaching;

            protected $table = 'has_caching_presentation_test';

            public function getCacheModuleName()
            {
                return 'TestModule';
            }

            public function getCacheModuleRouteName()
            {
                return 'TestRoute';
            }
        };
        $model->setRawAttributes(['id' => $id]);
        $model->exists = true;

        return $model;
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
