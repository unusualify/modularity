<?php

namespace Unusualify\Modularity\Tests\Models\Enums;

use Unusualify\Modularity\Entities\Enums\ProcessStatus;
use Unusualify\Modularity\Tests\TestCase;

class ProcessStatusTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'PREPARING' => 'preparing',
            'WAITING_FOR_CONFIRMATION' => 'waiting_for_confirmation',
            'WAITING_FOR_REACTION' => 'waiting_for_reaction',
            'REJECTED' => 'rejected',
            'CONFIRMED' => 'confirmed',
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, ProcessStatus::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(ProcessStatus::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = ProcessStatus::cases();
        $this->assertCount(5, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertContains('preparing', $caseValues);
        $this->assertContains('waiting_for_confirmation', $caseValues);
        $this->assertContains('waiting_for_reaction', $caseValues);
        $this->assertContains('rejected', $caseValues);
        $this->assertContains('confirmed', $caseValues);
    }

    public function test_get_static_method()
    {
        $this->assertEquals('preparing', ProcessStatus::get('PREPARING'));
        $this->assertEquals('waiting_for_confirmation', ProcessStatus::get('WAITING_FOR_CONFIRMATION'));
        $this->assertEquals('waiting_for_reaction', ProcessStatus::get('WAITING_FOR_REACTION'));
        $this->assertEquals('rejected', ProcessStatus::get('REJECTED'));
        $this->assertEquals('confirmed', ProcessStatus::get('CONFIRMED'));
        $this->assertNull(ProcessStatus::get('INVALID'));
    }

    public function test_label_method()
    {
        $this->assertEquals(__('Preparing'), ProcessStatus::PREPARING->label());
        $this->assertEquals(__('Waiting'), ProcessStatus::WAITING_FOR_CONFIRMATION->label());
        $this->assertEquals(__('Waiting'), ProcessStatus::WAITING_FOR_REACTION->label());
        $this->assertEquals(__('Rejected'), ProcessStatus::REJECTED->label());
        $this->assertEquals(__('Confirmed'), ProcessStatus::CONFIRMED->label());
    }

    public function test_color_method()
    {
        $this->assertEquals('info', ProcessStatus::PREPARING->color());
        $this->assertEquals('warning', ProcessStatus::WAITING_FOR_CONFIRMATION->color());
        $this->assertEquals('warning', ProcessStatus::WAITING_FOR_REACTION->color());
        $this->assertEquals('error', ProcessStatus::REJECTED->color());
        $this->assertEquals('success', ProcessStatus::CONFIRMED->color());
    }

    public function test_card_color_method()
    {
        $this->assertEquals('grey', ProcessStatus::PREPARING->cardColor());
        $this->assertEquals('blue-darken-1', ProcessStatus::WAITING_FOR_CONFIRMATION->cardColor());
        $this->assertEquals('blue-darken-1', ProcessStatus::WAITING_FOR_REACTION->cardColor());
        $this->assertEquals('red-darken-1', ProcessStatus::REJECTED->cardColor());
        $this->assertEquals('green-darken-1', ProcessStatus::CONFIRMED->cardColor());
    }

    public function test_card_variant_method()
    {
        $this->assertEquals('outlined', ProcessStatus::PREPARING->cardVariant());
        $this->assertEquals('outlined', ProcessStatus::WAITING_FOR_CONFIRMATION->cardVariant());
        $this->assertEquals('outlined', ProcessStatus::WAITING_FOR_REACTION->cardVariant());
        $this->assertEquals('tonal', ProcessStatus::REJECTED->cardVariant());
        $this->assertEquals('tonal', ProcessStatus::CONFIRMED->cardVariant());
    }

    public function test_icon_method()
    {
        $this->assertEquals('mdi-progress-clock', ProcessStatus::PREPARING->icon());
        $this->assertEquals('mdi-clock-check-outline', ProcessStatus::WAITING_FOR_CONFIRMATION->icon());
        $this->assertEquals('mdi-clock-check-outline', ProcessStatus::WAITING_FOR_REACTION->icon());
        $this->assertEquals('mdi-close-circle-outline', ProcessStatus::REJECTED->icon());
        $this->assertEquals('mdi-check-circle-outline', ProcessStatus::CONFIRMED->icon());
    }

    public function test_next_action_label_method()
    {
        $this->assertEquals(__('Send for Confirmation'), ProcessStatus::PREPARING->nextActionLabel());
        $this->assertEquals(__('Confirm'), ProcessStatus::WAITING_FOR_CONFIRMATION->nextActionLabel());
        $this->assertEquals(__('Confirm'), ProcessStatus::WAITING_FOR_REACTION->nextActionLabel());
        $this->assertEquals(__('Resend'), ProcessStatus::REJECTED->nextActionLabel());
        $this->assertEquals(__('Revert'), ProcessStatus::CONFIRMED->nextActionLabel());
    }

    public function test_status_reason_label_method()
    {
        $this->assertEquals(__('Preparing'), ProcessStatus::PREPARING->statusReasonLabel());
        $this->assertEquals(__('Arrangement'), ProcessStatus::WAITING_FOR_CONFIRMATION->statusReasonLabel());
        $this->assertEquals(__('Arrangement'), ProcessStatus::WAITING_FOR_REACTION->statusReasonLabel());
        $this->assertEquals(__('Reason'), ProcessStatus::REJECTED->statusReasonLabel());
        $this->assertEquals(__('Confirmation Reason'), ProcessStatus::CONFIRMED->statusReasonLabel());
    }

    public function test_next_action_color_method()
    {
        $this->assertEquals('secondary', ProcessStatus::PREPARING->nextActionColor());
        $this->assertEquals('success', ProcessStatus::WAITING_FOR_CONFIRMATION->nextActionColor());
        $this->assertEquals('success', ProcessStatus::WAITING_FOR_REACTION->nextActionColor());
        $this->assertEquals('secondary', ProcessStatus::REJECTED->nextActionColor());
        $this->assertEquals('grey-lighten-2', ProcessStatus::CONFIRMED->nextActionColor());
    }

    public function test_informational_message_method()
    {
        $this->assertEquals(__('The contents are being prepared or updated. Please check back later.'), ProcessStatus::PREPARING->informationalMessage());
        $this->assertEquals(__('The contents are being prepared or updated. Please check back later.'), ProcessStatus::WAITING_FOR_CONFIRMATION->informationalMessage());
        $this->assertEquals(__('The contents are being prepared or updated. Please check back later.'), ProcessStatus::WAITING_FOR_REACTION->informationalMessage());
        $this->assertEquals(__('The contents has been rejected. The reason is under review, you will be informed soon.'), ProcessStatus::REJECTED->informationalMessage());
        $this->assertEquals(__('The contents are confirmed.'), ProcessStatus::CONFIRMED->informationalMessage());
    }

    public function test_from_method_with_valid_values()
    {
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::from('preparing'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::from('waiting_for_confirmation'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::from('waiting_for_reaction'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::from('rejected'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::from('confirmed'));
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        ProcessStatus::from('invalid_status');
    }

    public function test_try_from_method_with_valid_values()
    {
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::tryFrom('preparing'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::tryFrom('waiting_for_confirmation'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::tryFrom('waiting_for_reaction'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::tryFrom('rejected'));
        $this->assertInstanceOf(ProcessStatus::class, ProcessStatus::tryFrom('confirmed'));
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(ProcessStatus::tryFrom('invalid_status'));
    }

    public function test_enum_comparison()
    {
        $preparing1 = ProcessStatus::PREPARING;
        $preparing2 = ProcessStatus::from('preparing');
        $confirmed = ProcessStatus::CONFIRMED;

        $this->assertTrue($preparing1 === $preparing2);
        $this->assertFalse($preparing1 === $confirmed);
        $this->assertTrue($preparing1 == $preparing2);
        $this->assertFalse($preparing1 == $confirmed);
    }

    public function test_enum_in_match_expression()
    {
        $status = ProcessStatus::CONFIRMED;

        $result = match ($status) {
            ProcessStatus::PREPARING => 'prep',
            ProcessStatus::WAITING_FOR_CONFIRMATION => 'wait_conf',
            ProcessStatus::WAITING_FOR_REACTION => 'wait_react',
            ProcessStatus::REJECTED => 'reject',
            ProcessStatus::CONFIRMED => 'confirm',
        };

        $this->assertEquals('confirm', $result);
    }

    public function test_enum_serialization()
    {
        $status = ProcessStatus::CONFIRMED;
        $serialized = serialize($status);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(ProcessStatus::class, $unserialized);
        $this->assertTrue($status === $unserialized);
        $this->assertEquals($status->value, $unserialized->value);
    }

    public function test_enum_json_serialization()
    {
        $status = ProcessStatus::PREPARING;
        $json = json_encode($status);

        $this->assertEquals('"preparing"', $json);
    }

    public function test_all_methods_return_strings()
    {
        $cases = ProcessStatus::cases();

        foreach ($cases as $case) {
            $this->assertIsString($case->label());
            $this->assertIsString($case->color());
            $this->assertIsString($case->cardColor());
            $this->assertIsString($case->cardVariant());
            $this->assertIsString($case->icon());
            $this->assertIsString($case->nextActionLabel());
            $this->assertIsString($case->statusReasonLabel());
            $this->assertIsString($case->nextActionColor());
            $this->assertIsString($case->informationalMessage());
        }
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('PREPARING', ProcessStatus::PREPARING->name);
        $this->assertEquals('WAITING_FOR_CONFIRMATION', ProcessStatus::WAITING_FOR_CONFIRMATION->name);
        $this->assertEquals('WAITING_FOR_REACTION', ProcessStatus::WAITING_FOR_REACTION->name);
        $this->assertEquals('REJECTED', ProcessStatus::REJECTED->name);
        $this->assertEquals('CONFIRMED', ProcessStatus::CONFIRMED->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals('preparing', ProcessStatus::PREPARING->value);
        $this->assertEquals('waiting_for_confirmation', ProcessStatus::WAITING_FOR_CONFIRMATION->value);
        $this->assertEquals('waiting_for_reaction', ProcessStatus::WAITING_FOR_REACTION->value);
        $this->assertEquals('rejected', ProcessStatus::REJECTED->value);
        $this->assertEquals('confirmed', ProcessStatus::CONFIRMED->value);
    }

    public function test_process_workflow_states()
    {
        // Test typical process workflow states
        $initialStates = [ProcessStatus::PREPARING];
        $waitingStates = [ProcessStatus::WAITING_FOR_CONFIRMATION, ProcessStatus::WAITING_FOR_REACTION];
        $finalStates = [ProcessStatus::REJECTED, ProcessStatus::CONFIRMED];

        foreach ($initialStates as $state) {
            $this->assertEquals('info', $state->color());
        }

        foreach ($waitingStates as $state) {
            $this->assertEquals('warning', $state->color());
        }

        foreach ($finalStates as $state) {
            $this->assertContains($state->color(), ['error', 'success']);
        }
    }

    public function test_card_styling_consistency()
    {
        // Test that card styling is consistent within state groups
        $outlinedStates = [
            ProcessStatus::PREPARING,
            ProcessStatus::WAITING_FOR_CONFIRMATION,
            ProcessStatus::WAITING_FOR_REACTION,
        ];

        $tonalStates = [
            ProcessStatus::REJECTED,
            ProcessStatus::CONFIRMED,
        ];

        foreach ($outlinedStates as $state) {
            $this->assertEquals('outlined', $state->cardVariant());
        }

        foreach ($tonalStates as $state) {
            $this->assertEquals('tonal', $state->cardVariant());
        }
    }

    public function test_icon_patterns()
    {
        // Test that similar states have consistent icon patterns
        $clockIcons = [ProcessStatus::PREPARING, ProcessStatus::WAITING_FOR_CONFIRMATION, ProcessStatus::WAITING_FOR_REACTION];
        $circleIcons = [ProcessStatus::REJECTED, ProcessStatus::CONFIRMED];

        foreach ($clockIcons as $status) {
            $this->assertStringContainsString('clock', $status->icon());
        }

        foreach ($circleIcons as $status) {
            $this->assertStringContainsString('circle', $status->icon());
        }
    }

    public function test_get_method_with_case_names()
    {
        // Test the static get method with all case names
        $cases = ProcessStatus::cases();

        foreach ($cases as $case) {
            $this->assertEquals($case->value, ProcessStatus::get($case->name));
        }
    }

    public function test_get_method_returns_null_for_invalid_case()
    {
        $this->assertNull(ProcessStatus::get('NON_EXISTENT_CASE'));
        $this->assertNull(ProcessStatus::get(''));
        $this->assertNull(ProcessStatus::get('preparing')); // lowercase should not match
    }

    public function test_waiting_states_have_same_label()
    {
        // Both waiting states should have the same label
        $this->assertEquals(
            ProcessStatus::WAITING_FOR_CONFIRMATION->label(),
            ProcessStatus::WAITING_FOR_REACTION->label()
        );
    }

    public function test_action_colors_are_valid_vuetify_colors()
    {
        $validColors = ['secondary', 'success', 'grey-lighten-2', 'primary'];
        $cases = ProcessStatus::cases();

        foreach ($cases as $case) {
            $this->assertContains($case->nextActionColor(), $validColors);
        }
    }
}
