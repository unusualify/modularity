<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Traits;

use Illuminate\Support\Collection;
use Unusualify\Modularous\Traits\ResponsiveVisibility;
use Unusualify\Modularous\Tests\TestCase;

class ResponsiveVisibilityTest extends TestCase
{
    private object $helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper = new class
        {
            use ResponsiveVisibility;
        };
    }

    public function test_get_responsive_items_for_arrays_and_collections(): void
    {
        $items = [
            ['title' => 'A', 'responsive' => ['hideOn' => 'sm']],
            ['title' => 'B'],
        ];

        $result = $this->helper->getResponsiveItems($items);

        $this->assertIsArray($result);
        $this->assertStringContainsString('d-sm-none', $result[0]['class']);

        $collectionResult = $this->helper->getResponsiveItems(collect($items));
        $this->assertInstanceOf(Collection::class, $collectionResult);
    }

    public function test_apply_responsive_classes_supports_object_settings(): void
    {
        $item = (object) [
            'class' => 'base',
            'responsive' => (object) ['showOn' => 'md'],
        ];

        $result = $this->helper->applyResponsiveClasses($item);

        $this->assertStringContainsString('d-none', $result->class);
        $this->assertStringContainsString('d-md-flex', $result->class);
    }

    public function test_generate_responsive_classes_ignores_invalid_settings(): void
    {
        $method = new \ReflectionMethod($this->helper, 'generateResponsiveClasses');
        $method->setAccessible(true);

        $this->assertSame([], $method->invoke($this->helper, 'invalid', 'flex'));
    }

    public function test_has_responsive_settings(): void
    {
        $this->assertTrue($this->helper->hasResponsiveSettings(['responsive' => ['hideOn' => 'lg']]));
        $this->assertFalse($this->helper->hasResponsiveSettings(['title' => 'plain']));
    }

    public function test_apply_responsive_classes_throws_for_invalid_display(): void
    {
        $this->expectException(\Exception::class);

        $this->helper->applyResponsiveClasses(['responsive' => ['showOn' => 'md']], null, 'grid');
    }

    public function test_get_responsive_items_throws_for_invalid_type(): void
    {
        $this->expectException(\Exception::class);

        $this->helper->getResponsiveItems('not-supported');
    }
}
