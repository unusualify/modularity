<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Contracts\CmsLocalizationContract;
use Modules\Cms\Localization\TranslatableCmsLocalizationAdapter;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsSitemapBuildService;
use Modules\Cms\Services\CmsSitemapCacheService;
use Unusualify\Modularous\Tests\TestCase;

class CmsSitemapBuildServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUrlRoutesTable();
        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
        $this->app['config']->set('modularous.cms_routing.path_segment_locales', ['en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');
        $this->app->instance(
            CmsLocalizationContract::class,
            new TranslatableCmsLocalizationAdapter(new CanonicalUrlResolver)
        );
    }

    public function test_build_produces_minimal_urlset_when_no_rows(): void
    {
        $build = $this->app->make(CmsSitemapBuildService::class);
        $xml = $build->buildXml();
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>', $xml);
        $this->assertStringNotContainsString('<url>', $xml);
    }

    public function test_cache_commit_and_persist(): void
    {
        $cache = $this->app->make(CmsSitemapCacheService::class);
        $key = 'modularous_cms_sitemap.committed_v1';
        $this->app['config']->set('modularous.cms_sitemap.cache_key', $key);
        Cache::store()->clear();
        $sample = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>' . "\n";
        $cache->commit($sample);
        $this->assertSame($sample, $cache->getCommittedXml());
    }

    public function test_empty_urlset_includes_stylesheet_pi(): void
    {
        $cache = $this->app->make(CmsSitemapCacheService::class);
        $xml = $cache->emptyUrlset();
        $this->assertStringContainsString('<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>', $xml);
        $this->assertStringContainsString('<urlset', $xml);
    }

    public function test_get_panel_item_rows_is_empty_without_urlable_data(): void
    {
        $build = $this->app->make(CmsSitemapBuildService::class);
        $rows = $build->getPanelItemRows();
        $this->assertIsArray($rows);
        $this->assertCount(0, $rows);
    }

    protected function createUrlRoutesTable(): void
    {
        $t = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();
            $table->unique(['locale', 'normalized_path']);
        });
    }
}
