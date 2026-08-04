<?php

namespace Unusualify\Modularous\Tests\Entities\Enums;

use Unusualify\Modularous\Entities\Enums\AssignmentStatus;
use Unusualify\Modularous\Tests\TestCase;

class AssignmentStatusTest extends TestCase
{
    public function test_cases_have_expected_values()
    {
        $this->assertEquals('completed', AssignmentStatus::COMPLETED->value);
        $this->assertEquals('pending', AssignmentStatus::PENDING->value);
        $this->assertEquals('rejected', AssignmentStatus::REJECTED->value);
        $this->assertEquals('cancelled', AssignmentStatus::CANCELLED->value);
    }

    public function test_it_lists_all_cases()
    {
        $this->assertSame([
            AssignmentStatus::COMPLETED,
            AssignmentStatus::PENDING,
            AssignmentStatus::REJECTED,
            AssignmentStatus::CANCELLED,
        ], AssignmentStatus::cases());
    }

    public function test_label_returns_translated_label()
    {
        $this->assertEquals(__('Completed'), AssignmentStatus::COMPLETED->label());
        $this->assertEquals(__('Pending'), AssignmentStatus::PENDING->label());
        $this->assertEquals(__('Rejected'), AssignmentStatus::REJECTED->label());
        $this->assertEquals(__('Cancelled'), AssignmentStatus::CANCELLED->label());
    }

    public function test_color_returns_expected_class()
    {
        $this->assertEquals('text-success', AssignmentStatus::COMPLETED->color());
        $this->assertEquals('text-warning', AssignmentStatus::PENDING->color());
        $this->assertEquals('text-error', AssignmentStatus::REJECTED->color());
        $this->assertEquals('text-grey', AssignmentStatus::CANCELLED->color());
    }

    public function test_icon_color_returns_expected_value()
    {
        $this->assertEquals('success', AssignmentStatus::COMPLETED->iconColor());
        $this->assertEquals('info', AssignmentStatus::PENDING->iconColor());
        $this->assertEquals('error', AssignmentStatus::REJECTED->iconColor());
        $this->assertEquals('grey', AssignmentStatus::CANCELLED->iconColor());
    }

    public function test_icon_returns_expected_value()
    {
        $this->assertEquals('mdi-check-circle-outline', AssignmentStatus::COMPLETED->icon());
        $this->assertEquals('mdi-clock-outline', AssignmentStatus::PENDING->icon());
        $this->assertEquals('mdi-close-circle-outline', AssignmentStatus::REJECTED->icon());
        $this->assertEquals('mdi-close-circle-outline', AssignmentStatus::CANCELLED->icon());
    }

    public function test_time_interval_description_returns_translated_value()
    {
        $this->assertEquals(__('Until'), AssignmentStatus::PENDING->timeIntervalDescription());
        $this->assertEquals(__('Rejected'), AssignmentStatus::REJECTED->timeIntervalDescription());
        $this->assertEquals(__('Cancelled'), AssignmentStatus::CANCELLED->timeIntervalDescription());
        $this->assertEquals(__('Completed'), AssignmentStatus::COMPLETED->timeIntervalDescription());
    }

    public function test_time_classes_returns_expected_value()
    {
        $this->assertEquals('font-weight-bold text-blue-darken-1', AssignmentStatus::PENDING->timeClasses());
        $this->assertEquals('font-weight-bold text-error', AssignmentStatus::REJECTED->timeClasses());
        $this->assertEquals('font-weight-bold text-warning', AssignmentStatus::CANCELLED->timeClasses());
        $this->assertEquals('font-weight-bold text-success', AssignmentStatus::COMPLETED->timeClasses());
    }
}
