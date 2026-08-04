<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Hydrates;

use Mockery;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Hydrates\Inputs\LayoutBladesHydrate;
use Unusualify\Modularous\Hydrates\Inputs\SlugHydrate;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Tests\TestCase;

class LayoutBladesAndSlugHydrateCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function layout_blades_hydrate_sets_type_without_preview_when_disabled(): void
    {
        config([
            'modularous.cms_layout_builder.preview_enabled' => false,
            'modularous.cms_page_layouts.layout_appends_modal_preview_enabled' => false,
        ]);

        $result = (new LayoutBladesHydrate([], null, null, true))->render();

        $this->assertSame('input-layout-blades', $result['type']);
        $this->assertArrayNotHasKey('shellDraftPreviewUrl', $result);
    }

    /** @test */
    public function layout_blades_hydrate_exposes_preview_url_when_layout_builder_preview_enabled(): void
    {
        config([
            'modularous.cms_layout_builder.preview_enabled' => true,
            'modularous.cms_page_layouts.layout_appends_modal_preview_enabled' => false,
        ]);

        \Illuminate\Support\Facades\Route::get('/preview', fn () => 'ok')->name('layout_builder.shell_draft_preview');
        \Illuminate\Support\Facades\Route::getRoutes()->refreshNameLookups();

        $result = (new LayoutBladesHydrate([], null, null, true))->render();

        $this->assertSame('input-layout-blades', $result['type']);
        $this->assertArrayHasKey('shellDraftPreviewUrl', $result);
        $this->assertNotEmpty($result['shellDraftPreviewUrl']);
    }

    /** @test */
    public function layout_blades_hydrate_uses_page_layout_appends_preview_mode_and_module_prefix(): void
    {
        config([
            'modularous.cms_layout_builder.preview_enabled' => false,
            'modularous.cms_page_layouts.layout_appends_modal_preview_enabled' => true,
        ]);

        $module = Mockery::mock(Module::class);
        $module->shouldReceive('panelRouteNamePrefix')->andReturn('admin.');

        \Illuminate\Support\Facades\Route::get('/admin-preview', fn () => 'ok')
            ->name('admin.layout_builder.shell_draft_preview');
        \Illuminate\Support\Facades\Route::getRoutes()->refreshNameLookups();

        $result = (new LayoutBladesHydrate([
            'shellPreviewMode' => 'page_layout_appends',
        ], $module, null, true))->render();

        $this->assertSame('input-layout-blades', $result['type']);
        $this->assertArrayHasKey('shellDraftPreviewUrl', $result);
    }

    /** @test */
    public function slug_hydrate_skips_parent_segment_when_module_missing(): void
    {
        Modularous::shouldReceive('find')->with('MissingModule')->andReturn(null);

        $result = (new SpiedSlugHydrate([
            '_moduleName' => 'MissingModule',
            '_routeName' => 'Page',
        ], null, null, true))->callAppendParentSegmentPrefixSchema([
            '_moduleName' => 'MissingModule',
            '_routeName' => 'Page',
            'type' => 'input-slug',
        ]);

        $this->assertArrayNotHasKey('parentSegmentPrefixByLocale', $result);
    }

    /** @test */
    public function slug_hydrate_resolves_null_model_fqcn_safely(): void
    {
        $module = Mockery::mock(Module::class);
        $module->shouldReceive('getModel')->andThrow(new \RuntimeException('missing'));

        $hydrate = new SpiedSlugHydrate([], null, null, true);
        $this->assertNull($hydrate->callResolveRouteModelFqcn($module, 'Page'));
    }
}

class SpiedSlugHydrate extends SlugHydrate
{
    public function callAppendParentSegmentPrefixSchema(array $input): array
    {
        return $this->appendParentSegmentPrefixSchema($input);
    }

    public function callResolveRouteModelFqcn($module, string $routeName): ?string
    {
        return $this->resolveRouteModelFqcn($module, $routeName);
    }
}
