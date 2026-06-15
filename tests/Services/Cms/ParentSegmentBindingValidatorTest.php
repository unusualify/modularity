<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\HomepageTest;
use Modules\Cms\Entities\Page;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Support\ParentSegmentBindingValidator;
use Unusualify\Modularous\Tests\TestCase;

class ParentSegmentBindingValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

            $table->unique(['target_model_class', 'locale'], 'cms_psb_model_locale_unq_test');
            $table->index(['target_model_class', 'locale', 'enabled'], 'cms_psb_model_loc_en_idx_test');
        });
    }

    public function test_locale_scopes_overlap_wildcards(): void
    {
        $this->assertTrue(ParentSegmentBindingValidator::localeScopesOverlap('en', ''));
        $this->assertTrue(ParentSegmentBindingValidator::localeScopesOverlap('', 'tr'));
        $this->assertTrue(ParentSegmentBindingValidator::localeScopesOverlap('fr', 'fr'));
        $this->assertFalse(ParentSegmentBindingValidator::localeScopesOverlap('en', 'de'));
    }

    public function test_second_empty_prefix_on_same_locale_for_different_model_is_rejected(): void
    {
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => '',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $this->expectException(ValidationException::class);

        ParentSegmentBindingValidator::assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
            true,
            HomepageTest::class,
            'en',
            '',
            null,
        );
    }

    public function test_empty_prefix_on_non_overlapping_specific_locales_allowed(): void
    {
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => '',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        ParentSegmentBindingValidator::assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
            true,
            HomepageTest::class,
            'tr',
            '',
            null,
        );

        ParentSegment::query()->create([
            'target_model_class' => HomepageTest::class,
            'locale' => 'tr',
            'normalized_prefix' => '',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(2, ParentSegment::query()->where('enabled', true)->whereRaw("TRIM(COALESCE(normalized_prefix, '')) = ?", [''])->count());
    }

    public function test_wildcard_locale_empty_prefix_conflicts_with_specific_locale_binding(): void
    {
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => '',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $this->expectException(ValidationException::class);

        ParentSegmentBindingValidator::assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
            true,
            HomepageTest::class,
            '',
            '',
            null,
        );
    }

    public function test_disabled_rows_do_not_block_empty_prefix_claim(): void
    {
        ParentSegment::query()->create([
            'target_model_class' => Page::class,
            'locale' => 'en',
            'normalized_prefix' => '',
            'enabled' => false,
            'sort_order' => 0,
        ]);

        ParentSegmentBindingValidator::assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
            true,
            HomepageTest::class,
            'en',
            '',
            null,
        );

        ParentSegment::query()->create([
            'target_model_class' => HomepageTest::class,
            'locale' => 'en',
            'normalized_prefix' => '',
            'enabled' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(1, ParentSegment::query()->where('enabled', true)->whereRaw("TRIM(COALESCE(normalized_prefix, '')) = ?", [''])->count());
    }
}
