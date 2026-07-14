<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Mockery;
use Unusualify\Modularous\Repositories\Traits\PositionTrait;
use Unusualify\Modularous\Tests\TestCase;

class PositionTraitTest extends TestCase
{
    public function test_filter_position_trait_sets_ordered_scope(): void
    {
        $repository = new class
        {
            use PositionTrait;
        };

        $query = Mockery::mock();
        $scopes = [];

        $repository->filterPositionTrait($query, $scopes);

        $this->assertTrue($scopes['ordered']);
    }
}
