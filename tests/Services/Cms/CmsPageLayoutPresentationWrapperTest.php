<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\Cms\Support\CmsPageLayoutPresentationWrapper;
use Modules\Cms\Support\CmsPublicFrontViewName;
use Unusualify\Modularous\Tests\TestCase;

class CmsPageLayoutPresentationWrapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(CmsPageLayoutPresentationWrapper::class)) {
            $this->markTestSkipped('Cms module is not loaded.');
        }

        Config::set('modularous.cms_features.enabled', true);
        Config::set('modularous.cms_page_layouts.enabled', true);
        Config::set('modularous.cms_page_layouts.filesystem_segments_without_db_binding_enabled', true);
        Config::set('modularous.cms_page_layouts.default_layout_builder_id', null);
        Config::set('modularous.cms_layout_builder.default_layout_slug', '');
        Config::set('modularous.cms_page_layouts.public_presentation_informational_fallback_enabled', false);

        View::addNamespace('business_package', __DIR__ . '/../../Support/views');
        // Minimal shell stubs (avoid SiteSettings / AnalyticsScripts from package shell).
        View::addNamespace('cms', __DIR__ . '/../../Support/views/cms_stub');
    }

    /** @test */
    public function document_or_null_renders_filesystem_page_layout_without_layout_builder_or_db_row(): void
    {
        $item = new class extends Model
        {
            use HasPageLayout;

            protected $table = 'package_countries';

            public $timestamps = false;

            public $name = 'Germany';
        };

        $html = CmsPageLayoutPresentationWrapper::documentOrNull(
            $item,
            'business_package::package_country.custom',
            ['item' => $item],
        );

        $this->assertNotNull($html);
        $this->assertStringContainsString('page-layout-body-fixture', (string) $html);
        $this->assertStringContainsString('Germany', (string) $html);
    }

    /** @test */
    public function document_or_null_returns_null_when_model_opts_out_of_page_layout(): void
    {
        $item = new class extends Model
        {
            protected $table = 'package_countries';

            public $timestamps = false;

            public static function supportsPageLayoutBindings(): bool
            {
                return false;
            }
        };

        $html = CmsPageLayoutPresentationWrapper::documentOrNull(
            $item,
            'business_package::package_country.custom',
            ['item' => $item],
        );

        $this->assertNull($html);
    }

    /** @test */
    public function resolve_view_name_uses_page_layout_body_when_custom_absent(): void
    {
        $tmp = sys_get_temp_dir() . '/modularous-pl-views-' . uniqid('', true);
        mkdir($tmp . '/only_layout/page_layout', 0777, true);
        file_put_contents(
            $tmp . '/only_layout/page_layout/body.blade.php',
            '<div>only-layout-body</div>',
        );

        View::addNamespace('tmp_cmr', $tmp);

        $viewName = CmsPublicFrontViewName::resolveViewNameForModuleRoute(
            'tmp_cmr::only_layout',
            [
                'module' => 'tmp_cmr',
                'route' => 'only_layout',
                'viewPrefix' => 'tmp_cmr::only_layout',
            ],
        );

        $this->assertSame('tmp_cmr::only_layout.page_layout.body', $viewName);

        array_map('unlink', glob($tmp . '/only_layout/page_layout/*') ?: []);
        @rmdir($tmp . '/only_layout/page_layout');
        @rmdir($tmp . '/only_layout');
        @rmdir($tmp);
    }
}
