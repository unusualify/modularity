<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Http\Request;
use Modules\Cms\Services\CanonicalUrlResolver;
use Unusualify\Modularous\Tests\TestCase;

class CanonicalUrlResolverSchemeTest extends TestCase
{
    public function test_resolve_uses_explicit_scheme_option(): void
    {
        config(['modularous.cms_routing.canonical_host' => 'frontend.test']);

        $resolver = new CanonicalUrlResolver;

        $out = $resolver->resolve('frontend.test', '/foo', 'en', [
            'scheme' => 'http',
            'redirect_to_canonical' => false,
        ]);

        $this->assertSame('http://frontend.test/foo', $out['canonical_url']);
    }

    public function test_resolve_uses_request_scheme_when_available(): void
    {
        config(['modularous.cms_routing.canonical_host' => 'frontend.test']);
        $this->app->instance('request', Request::create('http://frontend.test/foo', 'GET'));

        $resolver = new CanonicalUrlResolver;

        $out = $resolver->resolve('frontend.test', '/foo', 'en', [
            'redirect_to_canonical' => false,
        ]);

        $this->assertSame('http://frontend.test/foo', $out['canonical_url']);
    }

    public function test_resolve_falls_back_to_app_url_scheme(): void
    {
        config(['app.url' => 'http://frontend.b2press.test']);
        config(['modularous.cms_routing.canonical_host' => 'frontend.b2press.test']);

        $resolver = new CanonicalUrlResolver;

        $out = $resolver->resolve('frontend.b2press.test', '/bar', 'en', [
            'redirect_to_canonical' => false,
        ]);

        $this->assertSame('http://frontend.b2press.test/bar', $out['canonical_url']);
    }
}
