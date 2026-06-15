<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\UrlRoute;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Services\CmsUrlRouteRegistry;
use ReflectionMethod;
use Unusualify\Modularous\Tests\TestCase;

class CmsUrlRouteRegistryClaimTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createUrlRoutesTable();
        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
    }

    public function test_is_path_claimed_by_other_respects_excluded_page(): void
    {
        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        UrlRoute::query()->create([
            'locale' => 'en',
            'normalized_path' => '/shared-path',
            'urlable_type' => Page::class,
            'urlable_id' => 10,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertTrue($registry->isPathClaimedByOther('en', '/shared-path', Page::class, 20));
        $this->assertFalse($registry->isPathClaimedByOther('en', '/shared-path', Page::class, 10));
        $this->assertFalse($registry->isPathClaimedByOther('tr', '/shared-path', Page::class, 20));
    }

    public function test_is_path_claimed_by_other_detects_legacy_rows_without_leading_slash(): void
    {
        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        UrlRoute::query()->create([
            'locale' => 'tr',
            'normalized_path' => 'sayfalar/test',
            'urlable_type' => Page::class,
            'urlable_id' => 7,
            'kind' => UrlRoute::KIND_PAGE_PUBLIC,
        ]);

        $this->assertTrue($registry->isPathClaimedByOther('tr', '/sayfalar/test', Page::class, 99));
        $this->assertFalse($registry->isPathClaimedByOther('tr', '/sayfalar/test', Page::class, 7));
    }

    public function test_desired_public_paths_use_fallback_leaf_when_locale_slug_is_inactive(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);
        $this->app['config']->set('modularous.cms_parent_segments.enabled', false);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');
        $this->app['config']->set('translatable.fallback_locale', null);

        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        $page = new class extends Model
        {
            protected $table = 'stub_desired_public_paths';
        };

        $page->setRelation('slugs', collect([
            (object) ['locale' => 'tr', 'slug' => 'deneme', 'active' => false],
            (object) ['locale' => 'en', 'slug' => 'test', 'active' => true],
        ]));

        $method = new ReflectionMethod(CmsUrlRouteRegistry::class, 'desiredPublicPathsByLocale');
        $method->setAccessible(true);
        /** @var array<string, string> $paths */
        $paths = $method->invoke($registry, $page);

        $this->assertSame('/test', $paths['en']);
        $this->assertSame('/test', $paths['tr']);
        $this->assertCount(2, $paths);
    }

    public function test_desired_public_paths_emit_all_get_locales_when_only_subset_has_slug_rows(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);
        $this->app['config']->set('modularous.cms_parent_segments.enabled', false);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        $page = new class extends Model
        {
            protected $table = 'stub_desired_public_paths';
        };

        $page->setRelation('slugs', collect([
            (object) ['locale' => 'en', 'slug' => 'test', 'active' => true],
        ]));

        $method = new ReflectionMethod(CmsUrlRouteRegistry::class, 'desiredPublicPathsByLocale');
        $method->setAccessible(true);
        /** @var array<string, string> $paths */
        $paths = $method->invoke($registry, $page);

        $this->assertSame('/test', $paths['en']);
        $this->assertSame('/test', $paths['tr']);
        $this->assertCount(2, $paths);
    }

    public function test_desired_public_paths_empty_when_no_active_slug_segments(): void
    {
        $this->app['config']->set('modularous.cms_parent_segments.enabled', false);
        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        $page = new class extends Model
        {
            protected $table = 'stub_desired_public_paths';
        };

        $page->setRelation('slugs', collect([
            (object) ['locale' => 'tr', 'slug' => 'deneme', 'active' => false],
        ]));

        $method = new ReflectionMethod(CmsUrlRouteRegistry::class, 'desiredPublicPathsByLocale');
        $method->setAccessible(true);
        $paths = $method->invoke($registry, $page);

        $this->assertSame([], $paths);
    }

    public function test_sync_public_page_routes_for_all_models_early_returns_when_class_missing(): void
    {
        $this->expectNotToPerformAssertions();

        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));
        $registry->syncPublicPageRoutesForAllModelsOfClass('App\\Definitely\\NonexistentClassForRegistry999');
    }

    public function test_sync_public_page_routes_for_all_models_early_returns_when_model_lacks_slug_traits(): void
    {
        $this->expectNotToPerformAssertions();

        $canonical = app(CanonicalUrlResolverInterface::class);
        $registry = new CmsUrlRouteRegistry($canonical, new CmsParentSegmentResolver($canonical));

        $plain = new class extends Model
        {
            /** {@inheritdoc} */
            protected $table = 'stub_plain_sync_all';
        };

        $registry->syncPublicPageRoutesForAllModelsOfClass($plain::class);
    }

    protected function createUrlRoutesTable(): void
    {
        $t = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        Schema::dropIfExists($t);
        Schema::create($t, function (Blueprint $table): void {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'normalized_path']);
        });
    }
}
