<?php

namespace Unusualify\Modularous\Tests\Entities\Enums;

use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    public function test_cases_have_expected_values()
    {
        $this->assertEquals('PENDING', PaymentStatus::PENDING->value);
        $this->assertEquals('FAILED', PaymentStatus::FAILED->value);
        $this->assertEquals('CHECKOUT', PaymentStatus::CHECKOUT->value);
        $this->assertEquals('PROVISION', PaymentStatus::PROVISION->value);
        $this->assertEquals('COMPLETED', PaymentStatus::COMPLETED->value);
        $this->assertEquals('CANCELLED', PaymentStatus::CANCELLED->value);
        $this->assertEquals('REFUNDED', PaymentStatus::REFUNDED->value);
    }

    public function test_get_returns_value_for_known_case_name()
    {
        $this->assertEquals('PENDING', PaymentStatus::get('PENDING'));
        $this->assertEquals('REFUNDED', PaymentStatus::get('REFUNDED'));
    }

    public function test_get_returns_null_for_unknown_case_name()
    {
        $this->assertNull(PaymentStatus::get('UNKNOWN'));
        $this->assertNull(PaymentStatus::get('pending'));
    }

    public function test_label_returns_translated_label()
    {
        $this->assertEquals(__('Pending'), PaymentStatus::PENDING->label());
        $this->assertEquals(__('Failed'), PaymentStatus::FAILED->label());
        $this->assertEquals(__('Checkout'), PaymentStatus::CHECKOUT->label());
        $this->assertEquals(__('Provision'), PaymentStatus::PROVISION->label());
        $this->assertEquals(__('Completed'), PaymentStatus::COMPLETED->label());
        $this->assertEquals(__('Cancelled'), PaymentStatus::CANCELLED->label());
        $this->assertEquals(__('Refunded'), PaymentStatus::REFUNDED->label());
    }

    public function test_color_returns_expected_value()
    {
        $this->assertEquals('grey', PaymentStatus::PENDING->color());
        $this->assertEquals('warning', PaymentStatus::FAILED->color());
        $this->assertEquals('primary', PaymentStatus::CHECKOUT->color());
        $this->assertEquals('info', PaymentStatus::PROVISION->color());
        $this->assertEquals('success', PaymentStatus::COMPLETED->color());
        $this->assertEquals('error', PaymentStatus::CANCELLED->color());
        $this->assertEquals('grey', PaymentStatus::REFUNDED->color());
    }

    public function test_icon_returns_expected_value()
    {
        $this->assertEquals('mdi-clock-alert-outline', PaymentStatus::PENDING->icon());
        $this->assertEquals('mdi-close-circle-outline', PaymentStatus::FAILED->icon());
        $this->assertEquals('mdi-cart-outline', PaymentStatus::CHECKOUT->icon());
        $this->assertEquals('mdi-progress-clock', PaymentStatus::PROVISION->icon());
        $this->assertEquals('mdi-check-circle-outline', PaymentStatus::COMPLETED->icon());
        $this->assertEquals('mdi-close-circle-outline', PaymentStatus::CANCELLED->icon());
        $this->assertEquals('mdi-credit-card-refund-outline', PaymentStatus::REFUNDED->icon());
    }
}
