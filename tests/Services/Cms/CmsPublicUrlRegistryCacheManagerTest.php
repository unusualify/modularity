<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Cache;
use Modules\Cms\Support\CmsFrontRouteRegistrationCache;
use Modules\Cms\Support\CmsPublicUrlRegistryCacheManager;
use Modules\Cms\Support\CmsPublicUrlRegistryCoordinator;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicUrlRegistryCacheManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.cms_routing.public_url_registry_cache_store', 'array');
        $this->app['config']->set('modularous.cms_routing.front_route_registration_cache_enabled', true);
        $this->app['config']->set('modularous.cms_sitemap.cache_key', 'modularous_cms_sitemap.test_v1');

        Cache::store('array')->flush();
        Cache::flush();
        CmsFrontRouteRegistrationCache::forgetRuntime();
    }

    public function test_clear_resets_revision_counters_and_runtime(): void
    {
        CmsPublicUrlRegistryCoordinator::invalidateParentSegmentRegistry();
        CmsFrontRouteRegistrationCache::warm();

        $manager = $this->app->make(CmsPublicUrlRegistryCacheManager::class);
        $cleared = $manager->clear(revisions: true, routes: true, sitemap: false);

        $this->assertContains('revisions', $cleared);
        $this->assertContains('front_routes', $cleared);
        $this->assertSame('0', CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision());
    }

    public function test_cache_warms_front_routes_without_error(): void
    {
        $manager = $this->app->make(CmsPublicUrlRegistryCacheManager::class);
        $warmed = $manager->cache(routes: true, sitemap: false);

        $this->assertContains('front_routes', $warmed);
        $this->assertTrue(CmsFrontRouteRegistrationCache::usesUniversalPublicFront());
    }
}
