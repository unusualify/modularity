<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Support;

use Unusualify\Modularous\Support\BroadcastAvailability;
use Unusualify\Modularous\Tests\TestCase;

class BroadcastAvailabilityTest extends TestCase
{
    protected function tearDown(): void
    {
        BroadcastAvailability::clearFake();

        parent::tearDown();
    }

    public function test_is_enabled_respects_config_toggle(): void
    {
        config([
            'modularous.broadcasting.enabled' => false,
            'broadcasting.default' => 'null',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(true);

        $this->assertFalse(BroadcastAvailability::isConfigEnabled());
        $this->assertFalse(BroadcastAvailability::isEnabled());
    }

    public function test_is_enabled_when_config_on_and_null_driver(): void
    {
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'null',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(false);

        $this->assertTrue(BroadcastAvailability::isEnabled());
        $this->assertFalse(BroadcastAvailability::driverRequiresPusherSdk('null'));
    }

    public function test_is_enabled_false_when_reverb_and_pusher_sdk_missing(): void
    {
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'reverb',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(false);

        $this->assertTrue(BroadcastAvailability::driverRequiresPusherSdk());
        $this->assertFalse(BroadcastAvailability::pusherSdkAvailable());
        $this->assertFalse(BroadcastAvailability::isEnabled());
    }

    public function test_is_enabled_true_when_reverb_and_pusher_sdk_present(): void
    {
        config([
            'modularous.broadcasting.enabled' => true,
            'broadcasting.default' => 'reverb',
        ]);
        BroadcastAvailability::fakePusherSdkAvailable(true);

        $this->assertTrue(BroadcastAvailability::isEnabled());
    }

    public function test_pusher_driver_requires_sdk(): void
    {
        $this->assertTrue(BroadcastAvailability::driverRequiresPusherSdk('pusher'));
        $this->assertTrue(BroadcastAvailability::driverRequiresPusherSdk('reverb'));
        $this->assertFalse(BroadcastAvailability::driverRequiresPusherSdk('log'));
        $this->assertFalse(BroadcastAvailability::driverRequiresPusherSdk('redis'));
    }
}
