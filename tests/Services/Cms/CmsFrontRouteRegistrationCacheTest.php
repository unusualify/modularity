<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Cache;
use Modules\Cms\Support\CmsFrontRouteRegistrationCache;
use Modules\Cms\Support\CmsPublicUrlRegistryCoordinator;
use Unusualify\Modularous\Tests\TestCase;

class CmsFrontRouteRegistrationCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.cms_routing.front_route_registration_cache_enabled', true);
        $this->app['config']->set('modularous.cms_routing.universal_cms_public_front', true);
        $this->app['config']->set(
            'modularous.cms_routing.front_route_registration_cache_key',
            'modularous_cms.front_route_registration_test_v1'
        );
        $this->app['config']->set('modularous.cms_routing.public_url_registry_cache_store', 'array');

        Cache::store('array')->flush();
        CmsFrontRouteRegistrationCache::forgetRuntime();
    }

    public function test_universal_mode_does_not_scan_all_modules_for_catch_all_controller(): void
    {
        $this->assertTrue(CmsFrontRouteRegistrationCache::usesUniversalPublicFront());
        $this->assertSame([], CmsFrontRouteRegistrationCache::legacyQualifiedModules());
    }

    public function test_warm_persists_snapshot_in_configured_store(): void
    {
        CmsFrontRouteRegistrationCache::warm();

        $revision = CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision();
        $this->assertNotSame('', $revision);

        CmsFrontRouteRegistrationCache::forgetRuntime();
        $this->assertTrue(CmsFrontRouteRegistrationCache::usesUniversalPublicFront());
    }

    public function test_parent_segment_invalidation_bumps_revision(): void
    {
        CmsFrontRouteRegistrationCache::warm();
        $before = CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision();

        CmsPublicUrlRegistryCoordinator::invalidateParentSegmentRegistry();
        $after = CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision();

        $this->assertNotSame($before, $after);
    }

    public function test_persistent_cache_can_be_disabled(): void
    {
        $this->app['config']->set('modularous.cms_routing.front_route_registration_cache_enabled', false);

        CmsFrontRouteRegistrationCache::warm();

        $this->assertTrue(CmsFrontRouteRegistrationCache::usesUniversalPublicFront());
    }
}
