<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Unusualify\Modularous\Tests\TestCase;

class CmsParentSegmentResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->dropParentSegmentTables();

        parent::tearDown();
    }

    public function test_join_falls_back_to_leaf_when_tables_missing(): void
    {
        $resolver = new CmsParentSegmentResolver(new CanonicalUrlResolver);

        $path = $resolver->joinPublicLeafPath(Page::class, 'en', 'about');

        $this->assertSame('/about', $path);
    }

    public function test_resolves_prefix_and_joins_leaf(): void
    {
        $this->createParentSegmentTables();

        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => '',
            'normalized_prefix' => 'blog/kurumsal',
            'admin_label' => 'Blog',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $resolver = new CmsParentSegmentResolver(new CanonicalUrlResolver);

        $this->assertSame('/blog/kurumsal/hakkimizda', $resolver->joinPublicLeafPath(Page::class, 'en', 'hakkimizda'));
    }

    public function test_join_respects_explicit_empty_binding_as_locale_root(): void
    {
        $this->createParentSegmentTables();

        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => '',
            'normalized_prefix' => '',
            'admin_label' => 'Home',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $resolver = new CmsParentSegmentResolver(new CanonicalUrlResolver);

        $this->assertSame('/', $resolver->joinPublicLeafPath(Page::class, 'en', ''));

        $this->assertSame('/contact', $resolver->joinPublicLeafPath(Page::class, 'tr', 'contact'));
    }

    protected function createParentSegmentTables(): void
    {
        $bindings = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');

        Schema::dropIfExists($bindings);

        Schema::create($bindings, function (Blueprint $table): void {
            $table->id();
            $table->string('target_model_class', 512);
            $table->string('locale', 12)->default('');
            $table->string('normalized_prefix', 2048);
            $table->string('admin_label')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['target_model_class', 'locale'], 'cms_psb_model_locale_unq');
            $table->index(['target_model_class', 'locale', 'enabled'], 'cms_psb_model_loc_en_idx');
        });
    }

    protected function dropParentSegmentTables(): void
    {
        $bindings = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
        Schema::dropIfExists($bindings);

        $targets = modularousConfig('tables.cms_parent_segment_targets', 'um_cms_parent_segment_targets');
        $segments = modularousConfig('tables.cms_parent_segments', 'um_cms_parent_segments');
        Schema::dropIfExists($targets);
        Schema::dropIfExists($segments);
    }
}
