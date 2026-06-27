<?php

namespace Unusualify\Modularous\Tests\Support\Cms;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Support\CmsFrontRouteRegistrationCache;
use Modules\Cms\Support\CmsPublicUrlRegistryAboutReporter;
use Modules\Cms\Support\CmsPublicUrlRegistryCoordinator;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicUrlRegistryAboutReporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AboutCommand::flushState();
        CmsFrontRouteRegistrationCache::clearPersistent();
        CmsPublicUrlRegistryCoordinator::resetRevisionCounters();
        Cache::forget((string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1'));
    }

    public function test_report_lists_routing_cache_rows_when_public_front_is_enabled(): void
    {
        $report = app(CmsPublicUrlRegistryAboutReporter::class)->report();

        $this->assertArrayHasKey('Public Front', $report);
        $this->assertArrayHasKey('Front Routes', $report);
        $this->assertArrayHasKey('Registry Store', $report);
        $this->assertArrayHasKey('ParentSegment Revision', $report);
        $this->assertArrayHasKey('UrlRoute Revision', $report);
        $this->assertArrayHasKey('Sitemap', $report);
        $this->assertSame('0', $report['ParentSegment Revision']);
        $this->assertSame('0', $report['UrlRoute Revision']);
    }

    public function test_report_reflects_warmed_sitemap_cache(): void
    {
        Cache::forever(
            (string) modularousConfig('cms_sitemap.cache_key', 'modularous_cms_sitemap.committed_v1'),
            '<urlset></urlset>',
        );

        $report = app(CmsPublicUrlRegistryAboutReporter::class)->report();

        $this->assertArrayHasKey('Sitemap', $report);
        $this->assertSame(
            (string) modularousConfig('cms_routing.public_url_registry_cache_store', 'file'),
            $report['Registry Store'],
        );
    }

    public function test_report_omits_front_route_rows_when_auto_register_public_front_is_disabled(): void
    {
        config()->set('modularous.cms_routing.auto_register_public_front', false);

        $report = app(CmsPublicUrlRegistryAboutReporter::class)->report();

        $this->assertArrayNotHasKey('Front Routes', $report);
        $this->assertArrayNotHasKey('Public Front', $report);
        $this->assertArrayHasKey('Sitemap', $report);
    }
}
