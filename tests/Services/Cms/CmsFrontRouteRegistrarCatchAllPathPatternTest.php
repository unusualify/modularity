<?php

namespace Unusualify\Modularous\Tests\Services\Cms;

use Modules\Cms\Routing\CmsFrontRouteRegistrar;
use ReflectionMethod;
use Unusualify\Modularous\Tests\TestCase;

final class CmsFrontRouteRegistrarCatchAllPathPatternTest extends TestCase
{
    public function test_signed_preview_prefix_is_blocked_from_path_param_when_enabled(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', true);
        $this->app['config']->set('modularous.cms_routing.signed_preview.path_prefix', 'cms/preview');

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertMatchesRegularExpression($re, 'pages/about');
        $this->assertMatchesRegularExpression($re, '');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/preview/Cms/Page/1/tr');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/preview');
        $this->assertMatchesRegularExpression($re, 'cms/previewx/other');
    }

    public function test_extra_exclude_prefix_from_config_blocks_path(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', false);
        $this->app['config']->set('modularous.cms_routing.public_front_catch_all_exclude_path_prefixes', ['internal/widget']);

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertMatchesRegularExpression($re, 'foo');
        $this->assertDoesNotMatchRegularExpression($re, 'internal/widget/run');
        $this->assertDoesNotMatchRegularExpression($re, 'internal/widget');
    }

    public function test_stylesheet_public_path_prefix_blocks_when_route_enabled(): void
    {
        $this->app['config']->set('modularous.cms_routing.signed_preview.enabled', false);
        $this->app['config']->set('modularous.cms_stylesheets.public_route.enabled', true);
        $this->app['config']->set('modularous.cms_stylesheets.public_route.path_prefix', 'cms/stylesheets');
        $this->app['config']->set('modularous.cms_routing.public_front_catch_all_exclude_path_prefixes', []);

        $pattern = self::reflectCatchAllPattern();
        $re = '#' . str_replace('#', '\\#', $pattern) . '#';

        $this->assertDoesNotMatchRegularExpression($re, 'cms/stylesheets/demo.css');
        $this->assertDoesNotMatchRegularExpression($re, 'cms/stylesheets');
        $this->assertMatchesRegularExpression($re, 'pages/about');
    }

    /** @return non-empty-string */
    private static function reflectCatchAllPattern(): string
    {
        $m = new ReflectionMethod(CmsFrontRouteRegistrar::class, 'catchAllPathParameterPattern');
        $m->setAccessible(true);

        return (string) $m->invoke(null);
    }
}
