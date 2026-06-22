<?php

namespace Unusualify\Modularous\Tests\Entities\Enums;

use Unusualify\Modularous\Entities\Enums\ProcessStatus;
use Unusualify\Modularous\Tests\TestCase;

class ProcessStatusTest extends TestCase
{
    public function test_cases_have_expected_values()
    {
        $this->assertEquals('preparing', ProcessStatus::PREPARING->value);
        $this->assertEquals('waiting_for_confirmation', ProcessStatus::WAITING_FOR_CONFIRMATION->value);
        $this->assertEquals('waiting_for_reaction', ProcessStatus::WAITING_FOR_REACTION->value);
        $this->assertEquals('rejected', ProcessStatus::REJECTED->value);
        $this->assertEquals('confirmed', ProcessStatus::CONFIRMED->value);
    }

    public function test_get_returns_value_for_known_case_name()
    {
        $this->assertEquals('preparing', ProcessStatus::get('PREPARING'));
        $this->assertEquals('confirmed', ProcessStatus::get('CONFIRMED'));
    }

    public function test_get_returns_null_for_unknown_case_name()
    {
        $this->assertNull(ProcessStatus::get('UNKNOWN'));
        $this->assertNull(ProcessStatus::get('preparing'));
    }

    public function test_label_returns_translated_value()
    {
        $this->assertEquals(__('Preparing'), ProcessStatus::PREPARING->label());
        $this->assertEquals(__('Waiting'), ProcessStatus::WAITING_FOR_CONFIRMATION->label());
        $this->assertEquals(__('Waiting'), ProcessStatus::WAITING_FOR_REACTION->label());
        $this->assertEquals(__('Rejected'), ProcessStatus::REJECTED->label());
        $this->assertEquals(__('Confirmed'), ProcessStatus::CONFIRMED->label());
    }

    public function test_color_returns_expected_value()
    {
        $this->assertEquals('info', ProcessStatus::PREPARING->color());
        $this->assertEquals('warning', ProcessStatus::WAITING_FOR_CONFIRMATION->color());
        $this->assertEquals('warning', ProcessStatus::WAITING_FOR_REACTION->color());
        $this->assertEquals('error', ProcessStatus::REJECTED->color());
        $this->assertEquals('success', ProcessStatus::CONFIRMED->color());
    }

    public function test_card_color_returns_expected_value()
    {
        $this->assertEquals('grey', ProcessStatus::PREPARING->cardColor());
        $this->assertEquals('blue-darken-1', ProcessStatus::WAITING_FOR_CONFIRMATION->cardColor());
        $this->assertEquals('blue-darken-1', ProcessStatus::WAITING_FOR_REACTION->cardColor());
        $this->assertEquals('red-darken-1', ProcessStatus::REJECTED->cardColor());
        $this->assertEquals('green-darken-1', ProcessStatus::CONFIRMED->cardColor());
    }

    public function test_card_variant_returns_expected_value()
    {
        $this->assertEquals('outlined', ProcessStatus::PREPARING->cardVariant());
        $this->assertEquals('outlined', ProcessStatus::WAITING_FOR_CONFIRMATION->cardVariant());
        $this->assertEquals('outlined', ProcessStatus::WAITING_FOR_REACTION->cardVariant());
        $this->assertEquals('tonal', ProcessStatus::REJECTED->cardVariant());
        $this->assertEquals('tonal', ProcessStatus::CONFIRMED->cardVariant());
    }

    public function test_icon_returns_expected_value()
    {
        $this->assertEquals('mdi-progress-clock', ProcessStatus::PREPARING->icon());
        $this->assertEquals('mdi-clock-check-outline', ProcessStatus::WAITING_FOR_CONFIRMATION->icon());
        $this->assertEquals('mdi-clock-check-outline', ProcessStatus::WAITING_FOR_REACTION->icon());
        $this->assertEquals('mdi-close-circle-outline', ProcessStatus::REJECTED->icon());
        $this->assertEquals('mdi-check-circle-outline', ProcessStatus::CONFIRMED->icon());
    }

    public function test_next_action_label_returns_translated_value()
    {
        $this->assertEquals(__('Send for Confirmation'), ProcessStatus::PREPARING->nextActionLabel());
        $this->assertEquals(__('Confirm'), ProcessStatus::WAITING_FOR_CONFIRMATION->nextActionLabel());
        $this->assertEquals(__('Confirm'), ProcessStatus::WAITING_FOR_REACTION->nextActionLabel());
        $this->assertEquals(__('Resend'), ProcessStatus::REJECTED->nextActionLabel());
        $this->assertEquals(__('Revert'), ProcessStatus::CONFIRMED->nextActionLabel());
    }

    public function test_status_reason_label_returns_translated_value()
    {
        $this->assertEquals(__('Preparing'), ProcessStatus::PREPARING->statusReasonLabel());
        $this->assertEquals(__('Arrangement'), ProcessStatus::WAITING_FOR_CONFIRMATION->statusReasonLabel());
        $this->assertEquals(__('Arrangement'), ProcessStatus::WAITING_FOR_REACTION->statusReasonLabel());
        $this->assertEquals(__('Reason'), ProcessStatus::REJECTED->statusReasonLabel());
        $this->assertEquals(__('Confirmation Reason'), ProcessStatus::CONFIRMED->statusReasonLabel());
    }

    public function test_next_action_color_returns_expected_value()
    {
        $this->assertEquals('secondary', ProcessStatus::PREPARING->nextActionColor());
        $this->assertEquals('success', ProcessStatus::WAITING_FOR_CONFIRMATION->nextActionColor());
        $this->assertEquals('success', ProcessStatus::WAITING_FOR_REACTION->nextActionColor());
        $this->assertEquals('secondary', ProcessStatus::REJECTED->nextActionColor());
        $this->assertEquals('grey-lighten-2', ProcessStatus::CONFIRMED->nextActionColor());
    }

    public function test_informational_message_returns_translated_value()
    {
        $this->assertEquals(
            __('The contents are being prepared or updated. Please check back later.'),
            ProcessStatus::PREPARING->informationalMessage()
        );
        $this->assertEquals(
            __('The contents has been rejected. The reason is under review, you will be informed soon.'),
            ProcessStatus::REJECTED->informationalMessage()
        );
        $this->assertEquals(
            __('The contents are confirmed.'),
            ProcessStatus::CONFIRMED->informationalMessage()
        );
        // Cases without an explicit arm fall back to the default message.
        $this->assertEquals(
            __('The contents are being prepared or updated. Please check back later.'),
            ProcessStatus::WAITING_FOR_CONFIRMATION->informationalMessage()
        );
        $this->assertEquals(
            __('The contents are being prepared or updated. Please check back later.'),
            ProcessStatus::WAITING_FOR_REACTION->informationalMessage()
        );
    }
}
