<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Entities\Enums\PaymentStatus;
use Unusualify\Modularity\Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'PENDING' => 'PENDING',
            'FAILED' => 'FAILED',
            'COMPLETED' => 'COMPLETED',
            'CANCELLED' => 'CANCELLED',
            'REFUNDED' => 'REFUNDED',
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, PaymentStatus::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(PaymentStatus::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = PaymentStatus::cases();
        $this->assertCount(5, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertContains('PENDING', $caseValues);
        $this->assertContains('FAILED', $caseValues);
        $this->assertContains('COMPLETED', $caseValues);
        $this->assertContains('CANCELLED', $caseValues);
        $this->assertContains('REFUNDED', $caseValues);
    }

    public function test_get_static_method()
    {
        $this->assertEquals('PENDING', PaymentStatus::get('PENDING'));
        $this->assertEquals('FAILED', PaymentStatus::get('FAILED'));
        $this->assertEquals('COMPLETED', PaymentStatus::get('COMPLETED'));
        $this->assertEquals('CANCELLED', PaymentStatus::get('CANCELLED'));
        $this->assertEquals('REFUNDED', PaymentStatus::get('REFUNDED'));
        $this->assertNull(PaymentStatus::get('INVALID'));
    }

    public function test_label_method()
    {
        $this->assertEquals(__('Pending'), PaymentStatus::PENDING->label());
        $this->assertEquals(__('Failed'), PaymentStatus::FAILED->label());
        $this->assertEquals(__('Completed'), PaymentStatus::COMPLETED->label());
        $this->assertEquals(__('Cancelled'), PaymentStatus::CANCELLED->label());
        $this->assertEquals(__('Refunded'), PaymentStatus::REFUNDED->label());
    }

    public function test_color_method()
    {
        $this->assertEquals('grey', PaymentStatus::PENDING->color());
        $this->assertEquals('warning', PaymentStatus::FAILED->color());
        $this->assertEquals('success', PaymentStatus::COMPLETED->color());
        $this->assertEquals('error', PaymentStatus::CANCELLED->color());
        $this->assertEquals('grey', PaymentStatus::REFUNDED->color());
    }

    public function test_icon_method()
    {
        $this->assertEquals('mdi-clock-alert-outline', PaymentStatus::PENDING->icon());
        $this->assertEquals('mdi-close-circle-outline', PaymentStatus::FAILED->icon());
        $this->assertEquals('mdi-check-circle-outline', PaymentStatus::COMPLETED->icon());
        $this->assertEquals('mdi-close-circle-outline', PaymentStatus::CANCELLED->icon());
        $this->assertEquals('mdi-credit-card-refund-outline', PaymentStatus::REFUNDED->icon());
    }

    public function test_from_method_with_valid_values()
    {
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::from('PENDING'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::from('FAILED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::from('COMPLETED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::from('CANCELLED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::from('REFUNDED'));
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        PaymentStatus::from('INVALID_STATUS');
    }

    public function test_try_from_method_with_valid_values()
    {
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::tryFrom('PENDING'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::tryFrom('FAILED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::tryFrom('COMPLETED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::tryFrom('CANCELLED'));
        $this->assertInstanceOf(PaymentStatus::class, PaymentStatus::tryFrom('REFUNDED'));
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(PaymentStatus::tryFrom('INVALID_STATUS'));
    }

    public function test_enum_comparison()
    {
        $pending1 = PaymentStatus::PENDING;
        $pending2 = PaymentStatus::from('PENDING');
        $completed = PaymentStatus::COMPLETED;

        $this->assertTrue($pending1 === $pending2);
        $this->assertFalse($pending1 === $completed);
        $this->assertTrue($pending1 == $pending2);
        $this->assertFalse($pending1 == $completed);
    }

    public function test_enum_in_match_expression()
    {
        $status = PaymentStatus::COMPLETED;

        $result = match ($status) {
            PaymentStatus::PENDING => 'waiting',
            PaymentStatus::FAILED => 'error',
            PaymentStatus::COMPLETED => 'success',
            PaymentStatus::CANCELLED => 'stopped',
            PaymentStatus::REFUNDED => 'returned',
        };

        $this->assertEquals('success', $result);
    }

    public function test_enum_serialization()
    {
        $status = PaymentStatus::COMPLETED;
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(PaymentStatus::class, $unserialized);
        $this->assertTrue($status === $unserialized);
        $this->assertEquals($status->value, $unserialized->value);
    }

    public function test_enum_json_serialization()
    {
        $status = PaymentStatus::PENDING;
        $json = json_encode($status);

        $this->assertEquals('"PENDING"', $json);
    }

    public function test_all_methods_return_strings()
    {
        $cases = PaymentStatus::cases();

        foreach ($cases as $case) {
            $this->assertIsString($case->label());
            $this->assertIsString($case->color());
            $this->assertIsString($case->icon());
        }
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('PENDING', PaymentStatus::PENDING->name);
        $this->assertEquals('FAILED', PaymentStatus::FAILED->name);
        $this->assertEquals('COMPLETED', PaymentStatus::COMPLETED->name);
        $this->assertEquals('CANCELLED', PaymentStatus::CANCELLED->name);
        $this->assertEquals('REFUNDED', PaymentStatus::REFUNDED->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals('PENDING', PaymentStatus::PENDING->value);
        $this->assertEquals('FAILED', PaymentStatus::FAILED->value);
        $this->assertEquals('COMPLETED', PaymentStatus::COMPLETED->value);
        $this->assertEquals('CANCELLED', PaymentStatus::CANCELLED->value);
        $this->assertEquals('REFUNDED', PaymentStatus::REFUNDED->value);
    }

    public function test_payment_flow_states()
    {
        // Test typical payment flow states
        $initialStates = [PaymentStatus::PENDING];
        $successStates = [PaymentStatus::COMPLETED];
        $failureStates = [PaymentStatus::FAILED, PaymentStatus::CANCELLED];
        $postProcessStates = [PaymentStatus::REFUNDED];

        foreach ($initialStates as $state) {
            $this->assertEquals('grey', $state->color());
        }

        foreach ($successStates as $state) {
            $this->assertEquals('success', $state->color());
        }

        foreach ($failureStates as $state) {
            $this->assertContains($state->color(), ['warning', 'error']);
        }

        foreach ($postProcessStates as $state) {
            $this->assertEquals('grey', $state->color());
        }
    }

    public function test_icon_consistency()
    {
        // Test that similar states have consistent icon patterns
        $checkIcons = [PaymentStatus::COMPLETED];
        $closeIcons = [PaymentStatus::FAILED, PaymentStatus::CANCELLED];
        $clockIcons = [PaymentStatus::PENDING];
        $specialIcons = [PaymentStatus::REFUNDED];

        foreach ($checkIcons as $status) {
            $this->assertStringContainsString('check-circle', $status->icon());
        }

        foreach ($closeIcons as $status) {
            $this->assertStringContainsString('close-circle', $status->icon());
        }

        foreach ($clockIcons as $status) {
            $this->assertStringContainsString('clock', $status->icon());
        }

        foreach ($specialIcons as $status) {
            $this->assertStringContainsString('refund', $status->icon());
        }
    }

    public function test_get_method_with_case_names()
    {
        // Test the static get method with all case names
        $cases = PaymentStatus::cases();

        foreach ($cases as $case) {
            $this->assertEquals($case->value, PaymentStatus::get($case->name));
        }
    }

    public function test_get_method_returns_null_for_invalid_case()
    {
        $this->assertNull(PaymentStatus::get('NON_EXISTENT_CASE'));
        $this->assertNull(PaymentStatus::get(''));
        $this->assertNull(PaymentStatus::get('pending')); // lowercase should not match
    }

    public function test_uppercase_values_consistency()
    {
        // Test that all PaymentStatus values are uppercase
        $cases = PaymentStatus::cases();

        foreach ($cases as $case) {
            $this->assertEquals(mb_strtoupper($case->value), $case->value);
        }
    }

    public function test_payment_status_transitions()
    {
        // Test logical payment status transitions
        $validTransitions = [
            'PENDING' => ['COMPLETED', 'FAILED', 'CANCELLED'],
            'COMPLETED' => ['REFUNDED'],
            'FAILED' => ['PENDING'], // retry
            'CANCELLED' => ['PENDING'], // retry
            'REFUNDED' => [], // final state
        ];

        foreach ($validTransitions as $fromStatusValue => $toStatusValues) {
            $fromStatus = PaymentStatus::from($fromStatusValue);
            $this->assertInstanceOf(PaymentStatus::class, $fromStatus);

            foreach ($toStatusValues as $toStatusValue) {
                $toStatus = PaymentStatus::from($toStatusValue);
                $this->assertInstanceOf(PaymentStatus::class, $toStatus);
            }
        }
    }

    public function test_final_states()
    {
        // Test which states are considered final
        $finalStates = [PaymentStatus::COMPLETED, PaymentStatus::REFUNDED];
        $nonFinalStates = [PaymentStatus::PENDING, PaymentStatus::FAILED, PaymentStatus::CANCELLED];

        // Final states should have specific characteristics
        foreach ($finalStates as $state) {
            $this->assertNotEquals('warning', $state->color());
        }

        // Non-final states might be retryable
        foreach ($nonFinalStates as $state) {
            $this->assertInstanceOf(PaymentStatus::class, $state);
        }
    }
}
