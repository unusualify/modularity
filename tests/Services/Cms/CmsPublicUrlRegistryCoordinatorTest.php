<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Support\Facades\Cache;
use Modules\Cms\Support\CmsPublicUrlRegistryCoordinator;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicUrlRegistryCoordinatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('modularous.cms_routing.public_url_registry_cache_store', 'array');
        Cache::store('array')->flush();
    }

    public function test_parent_segment_revision_starts_at_zero_and_increments(): void
    {
        $this->assertSame('0', CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision());

        CmsPublicUrlRegistryCoordinator::invalidateParentSegmentRegistry();

        $this->assertSame('1', CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision());
    }

    public function test_url_route_revision_is_independent_from_parent_segment_revision(): void
    {
        CmsPublicUrlRegistryCoordinator::invalidateUrlRouteRegistry();

        $this->assertSame('0', CmsPublicUrlRegistryCoordinator::parentSegmentRegistryRevision());
        $this->assertSame('1', CmsPublicUrlRegistryCoordinator::urlRouteRegistryRevision());
    }
}
