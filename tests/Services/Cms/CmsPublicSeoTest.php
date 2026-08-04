<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Page;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Support\CmsPublicSeo;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
    }

    public function test_robots_defaults_to_index_follow_when_null(): void
    {
        $request = Request::create('https://example.test/cms/tr/foo', 'GET');
        $page = $this->makePage([
            'seo_title' => 'T',
            'title' => 'T',
            'seo_description' => null,
            'canonical_url' => null,
            'robots_index' => null,
            'robots_follow' => null,
        ]);

        $canonical = new CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $page, $canonical);

        $this->assertSame('index, follow', $out['robotsMeta']);
    }

    public function test_seo_index_false_maps_to_noindex_when_robots_index_unset(): void
    {
        $request = Request::create('https://example.test/blog/post', 'GET');

        $item = new class extends Model
        {
            protected $guarded = [];

            public $timestamps = false;
        };
        $item->forceFill([
            'seo_index' => false,
        ]);

        $canonical = new CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $item, $canonical);

        $this->assertSame('noindex, follow', $out['robotsMeta']);
    }

    public function test_staging_force_noindex_overrides_page_robots(): void
    {
        config(['modularous.cms_seo.staging.force_noindex' => true]);

        $resolved = CmsPublicSeo::resolveRobotsMeta(CmsPublicSeo::ROBOTS_INDEX_FOLLOW);

        $this->assertSame(CmsPublicSeo::ROBOTS_NOINDEX_NOFOLLOW, $resolved);
        $this->assertSame(CmsPublicSeo::ROBOTS_NOINDEX_NOFOLLOW, CmsPublicSeo::defaultRobotsMeta());
    }

    public function test_custom_canonical_absolute_is_preserved(): void
    {
        $request = Request::create('https://example.test/cms/foo', 'GET');
        $page = $this->makePage([
            'seo_title' => 'T',
            'title' => 'T',
            'canonical_url' => 'https://other.example/path',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $canonical = new CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $page, $canonical);

        $this->assertSame('https://other.example/path', $out['canonicalUrl']);
    }

    public function test_resolved_canonical_uses_app_url_scheme_when_http(): void
    {
        config(['app.url' => 'http://frontend.b2press.test']);
        config(['modularous.cms_routing.canonical_host' => 'frontend.b2press.test']);
        config(['modularous.cms_routing.front_route_prefix' => '']);
        config(['modularous.cms_routing.hide_default_locale_segment' => true]);
        config(['modularous.cms_routing.default_locale' => 'en']);

        $page = $this->makePage([
            'seo_title' => 'T',
            'title' => 'T',
            'canonical_url' => null,
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $canonical = new CanonicalUrlResolver;
        $out = CmsPublicSeo::buildForCache(
            'en',
            '/country-pr-packages/brazil',
            $page,
            $canonical,
        );

        $this->assertSame(
            'http://frontend.b2press.test/country-pr-packages/brazil',
            $out['canonicalUrl'],
        );
    }

    public function test_resolved_canonical_uses_request_scheme_on_live_requests(): void
    {
        config(['modularous.cms_routing.canonical_host' => 'frontend.b2press.test']);
        config(['modularous.cms_routing.front_route_prefix' => '']);

        $request = Request::create('http://frontend.b2press.test/country-pr-packages/brazil', 'GET');
        $page = $this->makePage([
            'seo_title' => 'T',
            'title' => 'T',
            'canonical_url' => null,
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $canonical = new CanonicalUrlResolver;
        $out = CmsPublicSeo::build($request, $page, $canonical);

        $this->assertSame(
            'http://frontend.b2press.test/country-pr-packages/brazil',
            $out['canonicalUrl'],
        );
    }

    /**
     * @param array<string, mixed> $translation
     */
    private function makePage(array $translation): Page
    {
        $page = new Page;
        $page->translateOrNew(app()->getLocale())->fill($translation);

        return $page;
    }
}
