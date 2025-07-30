<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Entities\Enums\DemandPriority;
use Unusualify\Modularity\Tests\TestCase;

class DemandPriorityTest extends TestCase
{
    public function test_enum_values()
    {
        $this->assertEquals('low', DemandPriority::LOW->value);
        $this->assertEquals('medium', DemandPriority::MEDIUM->value);
        $this->assertEquals('high', DemandPriority::HIGH->value);
        $this->assertEquals('urgent', DemandPriority::URGENT->value);
    }

    public function test_labels()
    {
        $this->assertEquals('Low', DemandPriority::LOW->label());
        $this->assertEquals('Medium', DemandPriority::MEDIUM->label());
        $this->assertEquals('High', DemandPriority::HIGH->label());
        $this->assertEquals('Urgent', DemandPriority::URGENT->label());
    }

    public function test_colors()
    {
        $this->assertEquals('text-grey', DemandPriority::LOW->color());
        $this->assertEquals('text-info', DemandPriority::MEDIUM->color());
        $this->assertEquals('text-warning', DemandPriority::HIGH->color());
        $this->assertEquals('text-error', DemandPriority::URGENT->color());
    }

    public function test_icon_colors()
    {
        $this->assertEquals('grey', DemandPriority::LOW->iconColor());
        $this->assertEquals('info', DemandPriority::MEDIUM->iconColor());
        $this->assertEquals('warning', DemandPriority::HIGH->iconColor());
        $this->assertEquals('error', DemandPriority::URGENT->iconColor());
    }

    public function test_icons()
    {
        $this->assertEquals('mdi-arrow-down', DemandPriority::LOW->icon());
        $this->assertEquals('mdi-minus', DemandPriority::MEDIUM->icon());
        $this->assertEquals('mdi-arrow-up', DemandPriority::HIGH->icon());
        $this->assertEquals('mdi-alert', DemandPriority::URGENT->icon());
    }

    public function test_order()
    {
        $this->assertEquals(1, DemandPriority::LOW->order());
        $this->assertEquals(2, DemandPriority::MEDIUM->order());
        $this->assertEquals(3, DemandPriority::HIGH->order());
        $this->assertEquals(4, DemandPriority::URGENT->order());
    }

    public function test_priority_ordering()
    {
        $priorities = [
            DemandPriority::URGENT,
            DemandPriority::LOW,
            DemandPriority::HIGH,
            DemandPriority::MEDIUM,
        ];

        // Sort by order method
        usort($priorities, fn($a, $b) => $a->order() <=> $b->order());

        $this->assertEquals(DemandPriority::LOW, $priorities[0]);
        $this->assertEquals(DemandPriority::MEDIUM, $priorities[1]);
        $this->assertEquals(DemandPriority::HIGH, $priorities[2]);
        $this->assertEquals(DemandPriority::URGENT, $priorities[3]);
    }
}
