<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Services\CanonicalUrlResolver;
use Unusualify\Modularous\Tests\TestCase;

class CanonicalUrlResolverTest extends TestCase
{
    public function test_it_builds_canonical_path_based_locale_urls(): void
    {
        config()->set('modularous.cms_routing.canonical_host', 'example.com');
        config()->set('modularous.cms_routing.default_locale', 'en');
        config()->set('modularous.cms_routing.hide_default_locale_segment', false);
        config()->set('modularous.cms_routing.redirect_to_canonical', true);

        $service = new CanonicalUrlResolver;
        $resolved = $service->resolve('example.com.tr', '/TR/About/', 'tr');

        $this->assertEquals('/tr/about', $resolved['canonical_path']);
        $this->assertTrue($resolved['should_redirect']);
        $this->assertEquals('http://example.com/tr/about', $resolved['redirect_to']);
    }
}
