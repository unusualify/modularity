<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Entities\Traits\Secondary;

use Unusualify\Modularous\Entities\Traits\Secondary\HasNesting;
use Unusualify\Modularous\Tests\TestCase;

class HasNestingTest extends TestCase
{
    /** @test */
    public function it_builds_nested_and_ancestor_slugs(): void
    {
        $parent = new class
        {
            public function getSlug($locale = null): string
            {
                return 'parent';
            }
        };

        $child = new class($parent)
        {
            use HasNesting;

            public $ancestors;

            public function __construct($parent)
            {
                $this->ancestors = [$parent];
            }

            public function getSlug($locale = null): string
            {
                return 'child';
            }
        };

        $this->assertSame('parent', $child->getAncestorsSlug());
        $this->assertSame('parent', $child->getAncestorsSlugAttribute());
        $this->assertSame('parent/child', $child->getNestedSlug());
        $this->assertSame('parent/child', $child->getNestedSlugAttribute());
    }

    /** @test */
    public function flatten_tree_returns_positional_nodes(): void
    {
        $subject = new class
        {
            use HasNesting;
        };

        $flat = $subject::flattenTree([
            ['id' => 1, 'children' => [
                ['id' => 2, 'children' => []],
            ]],
            ['id' => 3, 'children' => []],
        ]);

        $this->assertNotEmpty($flat);
        $this->assertTrue(collect($flat)->contains(fn ($node) => ($node['id'] ?? null) === 2));
    }
}
