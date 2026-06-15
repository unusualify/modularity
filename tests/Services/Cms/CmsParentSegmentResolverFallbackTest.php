<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Contracts\CanonicalUrlResolverInterface;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Unusualify\Modularous\Tests\TestCase;

class CmsParentSegmentResolverFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $table = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
        Schema::dropIfExists($table);
        Schema::create($table, function (Blueprint $table): void {
            $table->id();
            $table->string('target_model_class');
            $table->string('locale', 24)->nullable();
            $table->string('normalized_prefix', 2048)->nullable();
            $table->string('admin_label', 191)->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->app['config']->set('modularous.cms_parent_segments.enabled', true);
        $this->app->singleton(CanonicalUrlResolverInterface::class, CanonicalUrlResolver::class);
    }

    public function test_fallback_uses_enabled_default_locale_prefix_when_locale_binding_disabled(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'admin_label' => 'pages-en',
            'enabled' => true,
            'sort_order' => 10,
        ]);
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'tr',
            'normalized_prefix' => 'sayfalar',
            'admin_label' => 'pages-tr',
            'enabled' => false,
            'sort_order' => 11,
        ]);

        $resolver = new CmsParentSegmentResolver(app(CanonicalUrlResolverInterface::class));

        $this->assertSame('/pages/test', $resolver->joinPublicLeafPath(Page::class, 'tr', 'test'));
        $map = $resolver->normalizedPrefixesMapForTargetClass(Page::class);
        $this->assertSame('/pages', $map['tr'] ?? null);
        $this->assertSame('/pages', $map['en'] ?? null);
    }

    public function test_prefers_own_locale_binding_when_enabled(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'admin_label' => 'pages-en',
            'enabled' => true,
            'sort_order' => 10,
        ]);
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'tr',
            'normalized_prefix' => 'sayfalar',
            'admin_label' => 'pages-tr',
            'enabled' => true,
            'sort_order' => 11,
        ]);

        $resolver = new CmsParentSegmentResolver(app(CanonicalUrlResolverInterface::class));

        $this->assertSame('/sayfalar/test', $resolver->joinPublicLeafPath(Page::class, 'tr', 'test'));
        $map = $resolver->normalizedPrefixesMapForTargetClass(Page::class);
        $this->assertSame('/sayfalar', $map['tr'] ?? null);
    }

    public function test_no_prefix_when_no_enabled_binding_anywhere(): void
    {
        $this->app['config']->set('translatable.locales', ['tr', 'en']);
        $this->app['config']->set('modularous.cms_routing.default_locale', 'en');

        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => 'pages',
            'enabled' => false,
            'sort_order' => 1,
        ]);
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'tr',
            'normalized_prefix' => 'sayfalar',
            'enabled' => false,
            'sort_order' => 2,
        ]);

        $resolver = new CmsParentSegmentResolver(app(CanonicalUrlResolverInterface::class));

        $this->assertSame('/test', $resolver->joinPublicLeafPath(Page::class, 'tr', 'test'));
    }
}
