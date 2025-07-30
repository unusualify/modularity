<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Entities\Enums\DemandStatus;

class DemandStatusTest extends TestCase
{
    public function test_enum_values()
    {
        $this->assertEquals('pending', DemandStatus::PENDING->value);
        $this->assertEquals('evaluated', DemandStatus::EVALUATED->value);
        $this->assertEquals('in_review', DemandStatus::IN_REVIEW->value);
        $this->assertEquals('answered', DemandStatus::ANSWERED->value);
        $this->assertEquals('rejected', DemandStatus::REJECTED->value);
    }

    public function test_labels()
    {
        $this->assertEquals('Pending', DemandStatus::PENDING->label());
        $this->assertEquals('Evaluated', DemandStatus::EVALUATED->label());
        $this->assertEquals('In Review', DemandStatus::IN_REVIEW->label());
        $this->assertEquals('Answered', DemandStatus::ANSWERED->label());
        $this->assertEquals('Rejected', DemandStatus::REJECTED->label());
    }

    public function test_colors()
    {
        $this->assertEquals('text-warning', DemandStatus::PENDING->color());
        $this->assertEquals('text-info', DemandStatus::EVALUATED->color());
        $this->assertEquals('text-primary', DemandStatus::IN_REVIEW->color());
        $this->assertEquals('text-success', DemandStatus::ANSWERED->color());
        $this->assertEquals('text-error', DemandStatus::REJECTED->color());
    }

    public function test_icon_colors()
    {
        $this->assertEquals('warning', DemandStatus::PENDING->iconColor());
        $this->assertEquals('info', DemandStatus::EVALUATED->iconColor());
        $this->assertEquals('primary', DemandStatus::IN_REVIEW->iconColor());
        $this->assertEquals('success', DemandStatus::ANSWERED->iconColor());
        $this->assertEquals('error', DemandStatus::REJECTED->iconColor());
    }

    public function test_icons()
    {
        $this->assertEquals('mdi-clock-outline', DemandStatus::PENDING->icon());
        $this->assertEquals('mdi-eye-check-outline', DemandStatus::EVALUATED->icon());
        $this->assertEquals('mdi-magnify', DemandStatus::IN_REVIEW->icon());
        $this->assertEquals('mdi-check-circle-outline', DemandStatus::ANSWERED->icon());
        $this->assertEquals('mdi-close-circle-outline', DemandStatus::REJECTED->icon());
    }

    public function test_time_interval_descriptions()
    {
        $this->assertEquals('Submitted', DemandStatus::PENDING->timeIntervalDescription());
        $this->assertEquals('Evaluated', DemandStatus::EVALUATED->timeIntervalDescription());
        $this->assertEquals('Under Review', DemandStatus::IN_REVIEW->timeIntervalDescription());
        $this->assertEquals('Answered', DemandStatus::ANSWERED->timeIntervalDescription());
        $this->assertEquals('Rejected', DemandStatus::REJECTED->timeIntervalDescription());
    }

    public function test_time_classes()
    {
        $this->assertEquals('font-weight-bold text-warning', DemandStatus::PENDING->timeClasses());
        $this->assertEquals('font-weight-bold text-info', DemandStatus::EVALUATED->timeClasses());
        $this->assertEquals('font-weight-bold text-primary', DemandStatus::IN_REVIEW->timeClasses());
        $this->assertEquals('font-weight-bold text-success', DemandStatus::ANSWERED->timeClasses());
        $this->assertEquals('font-weight-bold text-error', DemandStatus::REJECTED->timeClasses());
    }

    public function test_allows_response()
    {
        $this->assertFalse(DemandStatus::PENDING->allowsResponse());
        $this->assertTrue(DemandStatus::EVALUATED->allowsResponse());
        $this->assertTrue(DemandStatus::IN_REVIEW->allowsResponse());
        $this->assertTrue(DemandStatus::ANSWERED->allowsResponse());
        $this->assertFalse(DemandStatus::REJECTED->allowsResponse());
    }

    public function test_is_active()
    {
        $this->assertTrue(DemandStatus::PENDING->isActive());
        $this->assertTrue(DemandStatus::EVALUATED->isActive());
        $this->assertTrue(DemandStatus::IN_REVIEW->isActive());
        $this->assertFalse(DemandStatus::ANSWERED->isActive());
        $this->assertFalse(DemandStatus::REJECTED->isActive());
    }
}
