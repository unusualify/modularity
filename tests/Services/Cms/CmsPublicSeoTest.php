<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

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
