<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\ErrorPage;

use Illuminate\Http\Request;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\ErrorPage\Support\ErrorPageDefaults;
use Modules\ErrorPage\Support\ErrorPageRenderer;
use ReflectionClass;
use Unusualify\Modularous\Tests\TestCase;

final class ErrorPageRendererTest extends TestCase
{
    public function test_body_view_candidates_are_layout_overrides_only(): void
    {
        $candidates = $this->renderer()->bodyViewCandidates('404', 'demo-theme');

        $this->assertSame([
            'cms.layout_builder.demo-theme.404',
            'cms::layout_builder.demo-theme.404',
        ], $candidates);
    }

    public function test_body_view_candidates_without_slug_are_empty(): void
    {
        $this->assertSame([], $this->renderer()->bodyViewCandidates('404', ''));
    }

    public function test_override_body_view_candidates_exclude_builtin(): void
    {
        $this->assertSame([
            'cms.layout_builder.demo-theme.403',
            'cms::layout_builder.demo-theme.403',
        ], $this->renderer()->overrideBodyViewCandidates('403', 'demo-theme'));

        $this->assertSame([], $this->renderer()->overrideBodyViewCandidates('403', ''));
    }

    public function test_defaults_catalog_includes_common_codes(): void
    {
        $this->assertSame(['404', '403', '500'], ErrorPageDefaults::errorCodes());
    }

    public function test_defaults_have_no_hardcoded_layout_slug(): void
    {
        $this->assertFalse(defined(ErrorPageDefaults::class.'::LAYOUT_BUILDER_SLUG'));

        config(['modularous.cms_layout_builder.default_layout_slug' => '']);

        $this->assertSame('', ErrorPageDefaults::configuredLayoutSlug());

        config(['modularous.cms_layout_builder.default_layout_slug' => 'my-theme']);

        $this->assertSame('my-theme', ErrorPageDefaults::configuredLayoutSlug());
    }

    public function test_resolve_strategy_no_record_uses_builtin(): void
    {
        $this->assertSame(ErrorPageRenderer::STRATEGY_BUILTIN, $this->renderer()->resolveStrategy(null));
    }

    public function test_resolve_strategy_unpublished_defers_to_laravel(): void
    {
        $this->assertNull($this->renderer()->resolveStrategy(false));
    }

    public function test_resolve_strategy_published_uses_cms(): void
    {
        $this->assertSame(ErrorPageRenderer::STRATEGY_CMS, $this->renderer()->resolveStrategy(true));
    }

    public function test_render_published_html_returns_null_when_unpublished(): void
    {
        $page = new \Modules\ErrorPage\Entities\ErrorPage([
            'name' => 'Error 404',
            'error_code' => '404',
            'published' => false,
        ]);
        $page->id = 99;
        $page->exists = true;

        $this->assertNull($this->renderer()->renderPublishedHtml($page));
    }

    public function test_to_response_returns_null_when_feature_disabled(): void
    {
        config(['modularous.cms_features.error_pages_enabled' => false]);

        $this->assertFalse($this->renderer()->enabled());
        $this->assertNull($this->renderer()->toResponse(404, Request::create('/missing')));
    }

    public function test_enabled_defaults_to_true(): void
    {
        config(['modularous.cms_features.error_pages_enabled' => true]);

        $this->assertTrue($this->renderer()->enabled());
    }

    public function test_builtin_view_name_matches_module_views(): void
    {
        $this->assertSame('error_page::error_page.404', ErrorPageDefaults::builtinViewName('404'));
        $this->assertSame('error_page::error_page.403', ErrorPageDefaults::builtinViewName('403'));
        $this->assertSame('error_page::error_page.500', ErrorPageDefaults::builtinViewName('500'));
    }

    public function test_builtin_standalone_bootstraps_store_for_core_free_mount(): void
    {
        $standalone = file_get_contents(
            realpath(__DIR__.'/../../../modules/ErrorPage/Resources/views/error_page/standalone.blade.php')
        );

        $this->assertIsString($standalone);
        $this->assertStringContainsString('id="admin"', $standalone);
        $this->assertStringContainsString('<v-app>', $standalone);
        $this->assertStringContainsString("withEntryPoints(['src/js/core-free.js'])", $standalone);
        $this->assertStringContainsString('languages: { all: [], active: {} }', $standalone);
        $this->assertStringNotContainsString('languages: [],', $standalone);
        // STORE must be defined before ModularousVite tags so deferred core-free can read it.
        $this->assertLessThan(
            strpos($standalone, 'withEntryPoints'),
            strpos($standalone, "window['{{ \$jsNamespace }}']")
        );
    }

    private function renderer(): ErrorPageRenderer
    {
        /** @var CmsPageLayoutResolver $resolver */
        $resolver = (new ReflectionClass(CmsPageLayoutResolver::class))->newInstanceWithoutConstructor();

        return new ErrorPageRenderer($resolver);
    }
}
