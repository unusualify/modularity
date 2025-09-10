<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Entities\Enums\AssignmentStatus;
use Unusualify\Modularity\Tests\TestCase;

class AssignmentStatusTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'COMPLETED' => 'completed',
            'PENDING' => 'pending',
            'REJECTED' => 'rejected',
            'CANCELLED' => 'cancelled',
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, AssignmentStatus::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(AssignmentStatus::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = AssignmentStatus::cases();
        $this->assertCount(4, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertContains('completed', $caseValues);
        $this->assertContains('pending', $caseValues);
        $this->assertContains('rejected', $caseValues);
        $this->assertContains('cancelled', $caseValues);
    }

    public function test_label_method()
    {
        $this->assertEquals(__('Completed'), AssignmentStatus::COMPLETED->label());
        $this->assertEquals(__('Pending'), AssignmentStatus::PENDING->label());
        $this->assertEquals(__('Rejected'), AssignmentStatus::REJECTED->label());
        $this->assertEquals(__('Cancelled'), AssignmentStatus::CANCELLED->label());
    }

    public function test_color_method()
    {
        $this->assertEquals('text-success', AssignmentStatus::COMPLETED->color());
        $this->assertEquals('text-warning', AssignmentStatus::PENDING->color());
        $this->assertEquals('text-error', AssignmentStatus::REJECTED->color());
        $this->assertEquals('text-grey', AssignmentStatus::CANCELLED->color());
    }

    public function test_icon_color_method()
    {
        $this->assertEquals('success', AssignmentStatus::COMPLETED->iconColor());
        $this->assertEquals('info', AssignmentStatus::PENDING->iconColor());
        $this->assertEquals('error', AssignmentStatus::REJECTED->iconColor());
        $this->assertEquals('grey', AssignmentStatus::CANCELLED->iconColor());
    }

    public function test_icon_method()
    {
        $this->assertEquals('mdi-check-circle-outline', AssignmentStatus::COMPLETED->icon());
        $this->assertEquals('mdi-clock-outline', AssignmentStatus::PENDING->icon());
        $this->assertEquals('mdi-close-circle-outline', AssignmentStatus::REJECTED->icon());
        $this->assertEquals('mdi-close-circle-outline', AssignmentStatus::CANCELLED->icon());
    }

    public function test_time_interval_description_method()
    {
        $this->assertEquals(__('Completed'), AssignmentStatus::COMPLETED->timeIntervalDescription());
        $this->assertEquals(__('Until'), AssignmentStatus::PENDING->timeIntervalDescription());
        $this->assertEquals(__('Rejected'), AssignmentStatus::REJECTED->timeIntervalDescription());
        $this->assertEquals(__('Cancelled'), AssignmentStatus::CANCELLED->timeIntervalDescription());
    }

    public function test_time_classes_method()
    {
        $this->assertEquals('font-weight-bold text-success', AssignmentStatus::COMPLETED->timeClasses());
        $this->assertEquals('font-weight-bold text-blue-darken-1', AssignmentStatus::PENDING->timeClasses());
        $this->assertEquals('font-weight-bold text-error', AssignmentStatus::REJECTED->timeClasses());
        $this->assertEquals('font-weight-bold text-warning', AssignmentStatus::CANCELLED->timeClasses());
    }

    public function test_from_method_with_valid_values()
    {
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::from('completed'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::from('pending'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::from('rejected'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::from('cancelled'));
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        AssignmentStatus::from('invalid_status');
    }

    public function test_try_from_method_with_valid_values()
    {
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::tryFrom('completed'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::tryFrom('pending'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::tryFrom('rejected'));
        $this->assertInstanceOf(AssignmentStatus::class, AssignmentStatus::tryFrom('cancelled'));
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(AssignmentStatus::tryFrom('invalid_status'));
    }

    public function test_enum_comparison()
    {
        $completed1 = AssignmentStatus::COMPLETED;
        $completed2 = AssignmentStatus::from('completed');
        $pending = AssignmentStatus::PENDING;

        $this->assertTrue($completed1 === $completed2);
        $this->assertFalse($completed1 === $pending);
        $this->assertTrue($completed1 == $completed2);
        $this->assertFalse($completed1 == $pending);
    }

    public function test_enum_in_match_expression()
    {
        $status = AssignmentStatus::PENDING;

        $result = match ($status) {
            AssignmentStatus::COMPLETED => 'done',
            AssignmentStatus::PENDING => 'waiting',
            AssignmentStatus::REJECTED => 'failed',
            AssignmentStatus::CANCELLED => 'stopped',
        };

        $this->assertEquals('waiting', $result);
    }

    public function test_enum_serialization()
    {
        $status = AssignmentStatus::COMPLETED;
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(AssignmentStatus::class, $unserialized);
        $this->assertTrue($status === $unserialized);
        $this->assertEquals($status->value, $unserialized->value);
    }

    public function test_enum_json_serialization()
    {
        $status = AssignmentStatus::PENDING;
        $json = json_encode($status);

        $this->assertEquals('"pending"', $json);
    }

    public function test_all_methods_return_strings()
    {
        $cases = AssignmentStatus::cases();

        foreach ($cases as $case) {
            $this->assertIsString($case->label());
            $this->assertIsString($case->color());
            $this->assertIsString($case->iconColor());
            $this->assertIsString($case->icon());
            $this->assertIsString($case->timeIntervalDescription());
            $this->assertIsString($case->timeClasses());
        }
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('COMPLETED', AssignmentStatus::COMPLETED->name);
        $this->assertEquals('PENDING', AssignmentStatus::PENDING->name);
        $this->assertEquals('REJECTED', AssignmentStatus::REJECTED->name);
        $this->assertEquals('CANCELLED', AssignmentStatus::CANCELLED->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals('completed', AssignmentStatus::COMPLETED->value);
        $this->assertEquals('pending', AssignmentStatus::PENDING->value);
        $this->assertEquals('rejected', AssignmentStatus::REJECTED->value);
        $this->assertEquals('cancelled', AssignmentStatus::CANCELLED->value);
    }
}
