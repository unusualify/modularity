<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

/**
 * Central gate for Modularous broadcasting.
 *
 * Enabled when {@see config('modularous.broadcasting.enabled')} is true and the
 * configured Laravel broadcast driver can be resolved without missing SDKs
 * (Pusher PHP client for `pusher` / `reverb` connections).
 */
final class BroadcastAvailability
{
    /**
     * Test override for {@see pusherSdkAvailable()}. Null = use class_exists.
     */
    private static ?bool $pusherSdkAvailableOverride = null;

    /**
     * Whether Modularous should run broadcasting (routes/channels, events, Echo).
     */
    public static function isEnabled(): bool
    {
        if (! self::isConfigEnabled()) {
            return false;
        }

        return self::driverSdkIsAvailable();
    }

    /**
     * Manual config toggle only ({@see modularous.broadcasting.enabled}).
     */
    public static function isConfigEnabled(): bool
    {
        return (bool) config('modularous.broadcasting.enabled', true);
    }

    /**
     * Whether the default broadcast driver can be constructed without missing packages.
     */
    public static function driverSdkIsAvailable(?string $driver = null): bool
    {
        $driver ??= (string) config('broadcasting.default', 'null');

        if (self::driverRequiresPusherSdk($driver)) {
            return self::pusherSdkAvailable();
        }

        return true;
    }

    /**
     * Laravel's pusher and reverb broadcasters require pusher/pusher-php-server.
     */
    public static function driverRequiresPusherSdk(?string $driver = null): bool
    {
        $driver ??= (string) config('broadcasting.default', 'null');

        return in_array($driver, ['pusher', 'reverb'], true);
    }

    public static function pusherSdkAvailable(): bool
    {
        if (self::$pusherSdkAvailableOverride !== null) {
            return self::$pusherSdkAvailableOverride;
        }

        return class_exists(\Pusher\Pusher::class);
    }

    /**
     * @internal Tests only.
     */
    public static function fakePusherSdkAvailable(?bool $available): void
    {
        self::$pusherSdkAvailableOverride = $available;
    }

    /**
     * @internal Tests only.
     */
    public static function clearFake(): void
    {
        self::$pusherSdkAvailableOverride = null;
    }
}
