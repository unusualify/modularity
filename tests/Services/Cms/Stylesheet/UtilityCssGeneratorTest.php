<?php

namespace Unusualify\Modularous\Tests\Services\Cms\Stylesheet;

use Modules\Cms\Services\Stylesheet\UtilityCssGenerator;
use Unusualify\Modularous\Tests\TestCase;

final class UtilityCssGeneratorTest extends TestCase
{
    public function test_spacing_generates_whitelisted_rules(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'spacing' => [
                'prefix' => 'u-',
                'scale' => ['1' => '0.25rem', '2' => '0.5rem'],
                'properties' => ['padding', 'gap'],
            ],
        ]);

        $this->assertStringContainsString('.u-p-1{padding:0.25rem;}', $css);
        $this->assertStringContainsString('.u-gap-2{gap:0.5rem;}', $css);
    }

    public function test_flex_presets_emit_sanitized_blocks(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'flex' => [
                'prefix' => 'u-flex-',
                'presets' => [
                    'center' => 'display:flex;align-items:center',
                ],
            ],
        ]);

        $this->assertStringContainsString('.u-flex-center{display:flex;align-items:center}', $css);
    }

    public function test_font_sizes_scale_prefix(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'fontSize' => [
                'prefix' => 'u-text-',
                'scale' => ['sm' => '.875rem'],
            ],
        ]);

        $this->assertStringContainsString('.u-text-sm{font-size:.875rem;}', $css);
    }

    public function test_color_scale_accepts_safe_values(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'color' => [
                'prefix' => 'u-tc-',
                'scale' => [
                    'primary' => '#336699',
                    'muted' => 'currentColor',
                ],
            ],
        ]);

        $this->assertStringContainsString('.u-tc-primary{color:#336699;}', $css);
        $this->assertStringContainsString('.u-tc-muted{color:currentColor;}', $css);
    }

    public function test_display_enumeration_whitelist(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'display' => [
                'prefix' => 'u-d-',
                'scale' => ['flex' => 'flex', 'hidden' => 'none'],
            ],
        ]);

        $this->assertStringContainsString('.u-d-flex{display:flex;}', $css);
        $this->assertStringContainsString('.u-d-hidden{display:none;}', $css);
    }

    public function test_aspect_ratio_accepts_fraction_or_auto(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'aspectRatio' => [
                'prefix' => 'u-ar-',
                'scale' => [
                    'video' => '16 / 9',
                    'fluid' => 'auto',
                ],
            ],
        ]);

        $this->assertStringContainsString('.u-ar-video{aspect-ratio:16/9;}', $css);
        $this->assertStringContainsString('.u-ar-fluid{aspect-ratio:auto;}', $css);
    }

    public function test_font_weight_numeric_whitelist(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'fontWeight' => [
                'prefix' => 'u-fw-',
                'scale' => ['semibold' => '600'],
            ],
        ]);

        $this->assertStringContainsString('.u-fw-semibold{font-weight:600;}', $css);
    }

    public function test_definition_class_prefix_overrides_default_namespace(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'class_prefix' => 'cms-',
            'width' => [
                'scale' => ['full' => '100%'],
            ],
        ]);

        $this->assertStringContainsString('.cms-w-full{width:100%;}', $css);
    }

    public function test_utility_class_prefix_alias_matches_class_prefix(): void
    {
        $gen = new UtilityCssGenerator;

        $css = $gen->generate([
            'utility_class_prefix' => 'tw-',
            'display' => [
                'scale' => ['flex' => 'flex'],
            ],
        ]);

        $this->assertStringContainsString('.tw-d-flex{display:flex;}', $css);
    }
}
