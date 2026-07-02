<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Support;

use Illuminate\Support\Facades\Log;

/**
 * Structured logging for modularous resource cache invalidation and warmup.
 *
 * Enable via {@see config('modularous.cache.logging')}. Apps may define the
 * {@code modularous-resource-cache} channel in config/logging.php or rely on
 * the default channel registered in {@see BaseServiceProvider::addModularousLogChannels()}.
 */
final class ModularousCacheLogger
{
    private static ?string $channelOverride = null;

    public static function setChannelOverride(?string $channel): void
    {
        self::$channelOverride = $channel !== '' ? $channel : null;
    }

    public static function clearChannelOverride(): void
    {
        self::$channelOverride = null;
    }

    public static function enabled(): bool
    {
        $configured = config('modularous.cache.logging.enabled');

        if ($configured === false) {
            return false;
        }

        if ($configured === true) {
            return self::resolveChannel() !== null;
        }

        return self::resolveChannel() !== null;
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function resolveChannel(): ?string
    {
        $channel = self::$channelOverride ?? config('modularous.cache.logging.channel', 'modularous-resource-cache');

        if (! is_string($channel) || $channel === '') {
            return null;
        }

        if (! config("logging.channels.{$channel}")) {
            return null;
        }

        return $channel;
    }

    private static function log(string $level, string $message, array $context): void
    {
        if (! self::enabled()) {
            return;
        }

        $channel = self::resolveChannel();

        if ($channel === null) {
            return;
        }

        Log::channel($channel)->{$level}($message, $context);
    }
}
