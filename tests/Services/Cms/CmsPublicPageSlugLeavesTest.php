<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Support\CmsPublicPageSlugLeaves;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Tests\TestCase;

class CmsPublicPageSlugLeavesTest extends TestCase
{
    public function test_css_leaf_maps_empty_to_index(): void
    {
        $this->assertSame('index', CmsPublicPageSlugLeaves::cssLeaf(''));
        $this->assertSame('index', CmsPublicPageSlugLeaves::cssLeaf(' / '));
    }

    public function test_css_leaf_uses_last_segment_for_nested_prefix(): void
    {
        $this->assertSame('about-b2press', CmsPublicPageSlugLeaves::cssLeaf('about-b2press'));
        $this->assertSame('hakkimizda', CmsPublicPageSlugLeaves::cssLeaf('sayfalar/hakkimizda'));
    }

    public function test_pick_fallback_prefers_default_then_en(): void
    {
        config(['modularous.cms_routing.default_locale' => 'en']);
        config(['translatable.fallback_locale' => 'en']);

        $leaf = CmsPublicPageSlugLeaves::pickFallbackLeaf([
            'tr' => 'b2press-hakkinda',
            'en' => 'about-b2press',
            'nl' => 'over-b2press',
        ], 'tr');

        $this->assertSame('about-b2press', $leaf);
    }

    public function test_parent_segment_item_exposes_fallback_css_slug(): void
    {
        app()->setLocale('tr');
        config(['modularous.cms_routing.default_locale' => 'en']);
        config(['translatable.fallback_locale' => 'en']);

        $item = new class extends Model
        {
            use HasParentSegment;

            public function parentSegments(): Collection
            {
                return new EloquentCollection([
                    $this->makeSegment('en', 'about-b2press'),
                    $this->makeSegment('tr', 'b2press-hakkinda'),
                    $this->makeSegment('nl', 'over-b2press'),
                ]);
            }

            private function makeSegment(string $locale, string $prefix): ParentSegment
            {
                $segment = new ParentSegment;
                $segment->locale = $locale;
                $segment->normalized_prefix = $prefix;

                return $segment;
            }
        };

        $resolved = CmsPublicPageSlugLeaves::forItem($item);

        $this->assertSame('b2press-hakkinda', $resolved['pageSlug']);
        $this->assertSame('about-b2press', $resolved['pageFallbackSlug']);
        $this->assertSame([
            'en' => 'about-b2press',
            'tr' => 'b2press-hakkinda',
            'nl' => 'over-b2press',
        ], $resolved['pageSlugs']);
    }

    public function test_home_empty_prefix_becomes_index_css_slug(): void
    {
        app()->setLocale('en');
        config(['modularous.cms_routing.default_locale' => 'en']);

        $item = new class extends Model
        {
            use HasParentSegment;

            public function parentSegments(): Collection
            {
                return new EloquentCollection([
                    tap(new ParentSegment, static function (ParentSegment $segment): void {
                        $segment->locale = 'en';
                        $segment->normalized_prefix = '';
                    }),
                    tap(new ParentSegment, static function (ParentSegment $segment): void {
                        $segment->locale = 'tr';
                        $segment->normalized_prefix = '';
                    }),
                ]);
            }
        };

        $resolved = CmsPublicPageSlugLeaves::forItem($item);

        $this->assertSame('index', $resolved['pageSlug']);
        $this->assertSame('index', $resolved['pageFallbackSlug']);
        $this->assertSame(['en' => '', 'tr' => ''], $resolved['pageSlugs']);
    }

    public function test_has_slug_item_uses_active_slug_leaves(): void
    {
        app()->setLocale('tr');
        config(['modularous.cms_routing.default_locale' => 'en']);
        config(['translatable.fallback_locale' => 'en']);

        $item = new class extends Model
        {
            use HasSlug;

            public function __construct()
            {
                parent::__construct();
                $this->setRelation('slugs', new EloquentCollection([
                    (object) ['locale' => 'en', 'slug' => 'my-post', 'active' => true],
                    (object) ['locale' => 'tr', 'slug' => 'yazi', 'active' => true],
                    (object) ['locale' => 'nl', 'slug' => 'old', 'active' => false],
                ]));
            }

            public function getSlugClass()
            {
                return \stdClass::class;
            }
        };

        $resolved = CmsPublicPageSlugLeaves::forItem($item);

        $this->assertSame('yazi', $resolved['pageSlug']);
        $this->assertSame('my-post', $resolved['pageFallbackSlug']);
        $this->assertSame([
            'en' => 'my-post',
            'tr' => 'yazi',
        ], $resolved['pageSlugs']);
    }
}
