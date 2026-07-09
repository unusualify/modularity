<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Support\CmsPublicSiteUrl;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicSiteUrlTest extends TestCase
{
    public function test_absolute_url_uses_public_front_route_domain_when_set(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_domain', 'frontend.example.test');
        $this->app['config']->set('app.url', 'http://admin.example.test');

        $url = CmsPublicSiteUrl::absoluteUrlForPath('/en/blog/post');

        $this->assertSame('http://frontend.example.test/en/blog/post', $url);
    }

    public function test_resolve_host_prefers_public_front_domain_over_canonical(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_domain', 'a.test');
        $this->app['config']->set('modularous.cms_routing.canonical_host', 'b.test');

        $this->assertSame('a.test', CmsPublicSiteUrl::resolvePublicSiteHost());
    }

    public function test_absolute_url_falls_back_to_url_helper_when_no_host_resolved(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_domain', null);
        $this->app['config']->set('modularous.cms_routing.canonical_host', '');
        $this->app['config']->set('modularous.cms_routing.bind_public_routes_to_app_url_host', false);

        $url = CmsPublicSiteUrl::absoluteUrlForPath('/tr/foo');

        $this->assertStringEndsWith('/tr/foo', $url);
    }

    public function test_resolve_public_site_root_url_prefers_public_front_route_domain(): void
    {
        $this->app['config']->set('modularous.cms_routing.public_front_route_domain', 'frontend.example.test');
        $this->app['config']->set('app.url', 'http://admin.example.test');

        $this->assertSame('http://frontend.example.test', CmsPublicSiteUrl::resolvePublicSiteRootUrl());
    }

    public function test_run_with_forced_public_root_url_restores_url_generator_after_callback(): void
    {
        $this->app['config']->set('app.url', 'http://frontend.example.test');
        $this->app['config']->set('modularous.cms_routing.canonical_host', 'frontend.example.test');
        $this->app->instance('request', \Illuminate\Http\Request::create('http://cms.example.test/admin', 'GET'));

        CmsPublicSiteUrl::runWithForcedPublicRootUrl(fn () => null);

        $this->assertStringContainsString(
            'cms.example.test',
            (string) url('/after-restore'),
        );
    }
}
