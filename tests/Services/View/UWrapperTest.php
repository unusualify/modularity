<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\View;

use BadMethodCallException;
use Unusualify\Modularous\Services\View\UComponent;
use Unusualify\Modularous\Services\View\UWrapper;
use Unusualify\Modularous\Tests\TestCase;

class UWrapperTest extends TestCase
{
    public function test_make_grid_section_builds_row_with_columns(): void
    {
        $button = UComponent::makeVBtn(['color' => 'primary']);

        $grid = UWrapper::makeGridSection([
            $button,
            ['content' => ['Nested']],
        ], ['class' => 'gy-4'], ['cols' => 12, 'lg' => 6]);

        $this->assertSame('v-row', $grid['tag']);
        $this->assertCount(2, $grid['elements']);
        $this->assertSame('v-col', $grid['elements'][0]['tag']);
        $this->assertSame('v-col', $grid['elements'][1]['tag']);
        $this->assertSame(['class' => 'gy-4'], $grid['attributes']);
    }

    public function test_make_form_wrapper_maps_forms_to_ue_form_components(): void
    {
        $grid = UWrapper::makeFormWrapper([
            ['name' => 'profile'],
            ['name' => 'security'],
        ]);

        $this->assertSame('v-row', $grid['tag']);
        $this->assertCount(2, $grid['elements']);
        $this->assertSame('v-col', $grid['elements'][0]['tag']);
        $this->assertSame('ue-form', $grid['elements'][0]['elements'][0]['tag']);
        $this->assertSame(['name' => 'profile'], $grid['elements'][0]['elements'][0]['attributes']);
    }

    public function test_make_grid_section_adds_multiple_children_when_content_has_more_than_one_item(): void
    {
        $first = UComponent::makeVBtn(['color' => 'primary']);
        $second = UComponent::makeVIcon(['icon' => 'mdi-check']);

        $grid = UWrapper::makeGridSection([
            [
                'content' => [$first, $second],
                'parent_attributes' => ['class' => 'nested-col'],
            ],
        ]);

        $this->assertCount(2, $grid['elements'][0]['elements']);
        $this->assertSame('v-btn', $grid['elements'][0]['elements'][0]['tag']);
        $this->assertSame('v-icon', $grid['elements'][0]['elements'][1]['tag']);
        $this->assertSame(['class' => 'nested-col', 'cols' => 12, 'lg' => 6], $grid['elements'][0]['attributes']);
    }

    public function test_static_call_to_unknown_method_throws_bad_method_call_exception(): void
    {
        $this->expectException(BadMethodCallException::class);

        UWrapper::unknownStaticFactory();
    }

    public function test_instance_call_to_unknown_method_throws_bad_method_call_exception(): void
    {
        $this->expectException(BadMethodCallException::class);

        UWrapper::make()->unknownInstanceFactory();
    }
}
