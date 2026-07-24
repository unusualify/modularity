<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\SystemConsole;

use Unusualify\Modularous\Facades\SystemSettings;

/**
 * Maps modularous.system_console config into UI / ArtisanRunner-friendly payloads.
 */
final class SystemConsoleConfig
{
    /** @var list<string> */
    private const DOWN_OPTION_KEYS = ['render', 'retry', 'refresh', 'secret'];

    /**
     * @return list<array{key: string, label: string, options: array<string, scalar>}>
     */
    public static function downPresetsForUi(): array
    {
        /** @var array<string, mixed> $presets */
        $presets = (array) modularousConfig('system_console.down_presets', []);
        $settingsOverrides = self::downPresetOverridesFromSettings();
        $defaultKey = (string) modularousConfig('system_console.default_down_preset', 'default');

        $items = [];
        $seen = [];
        foreach ($presets as $key => $preset) {
            if (! is_array($preset)) {
                continue;
            }

            $presetKey = (string) $key;
            if ($presetKey === '' || isset($seen[$presetKey])) {
                continue;
            }
            $seen[$presetKey] = true;

            $label = (string) ($preset['label'] ?? $presetKey);
            $options = self::normalizeOptions((array) ($preset['options'] ?? []));

            if ($presetKey === $defaultKey || ($defaultKey === '' && $presetKey === 'default')) {
                $options = self::mergeDownOptions($options, $settingsOverrides);
            }

            $items[] = [
                'key' => $presetKey,
                'label' => $label,
                'options' => $options,
            ];
        }

        return $items;
    }

    public static function defaultDownPresetKey(): string
    {
        $default = (string) modularousConfig('system_console.default_down_preset', 'default');
        $keys = array_column(self::downPresetsForUi(), 'key');

        if ($keys === []) {
            return '';
        }

        return in_array($default, $keys, true) ? $default : $keys[0];
    }

    /**
     * @return list<array{
     *     command: string,
     *     label: string,
     *     confirm: bool,
     *     color: string|null,
     *     variant: string|null,
     *     icon: string|null
     * }>
     */
    public static function cacheCommandsForUi(): array
    {
        /** @var array<int|string, mixed> $commands */
        $commands = (array) modularousConfig('system_console.cache_commands', []);

        $items = [];
        $seen = [];

        foreach ($commands as $command) {
            if (! is_array($command) || empty($command['command'])) {
                continue;
            }

            $name = (string) $command['command'];
            if (isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;

            $items[] = [
                'command' => $name,
                'label' => (string) ($command['label'] ?? $name),
                'confirm' => (bool) ($command['confirm'] ?? false),
                'color' => isset($command['color']) ? (string) $command['color'] : null,
                'variant' => isset($command['variant']) ? (string) $command['variant'] : 'tonal',
                'icon' => isset($command['icon']) ? (string) $command['icon'] : null,
            ];
        }

        return $items;
    }

    public static function isInMaintenance(): bool
    {
        try {
            return (bool) app()->isDownForMaintenance();
        } catch (\Throwable) {
            return is_file(storage_path('framework/down'));
        }
    }

    /**
     * Drop null/empty option values so ArtisanRunner omits unused flags.
     *
     * @param array<string, mixed> $options
     * @return array<string, scalar>
     */
    public static function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
                $normalized[(string) $name] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Non-empty SystemSettings overrides for artisan down options.
     *
     * @return array<string, scalar>
     */
    public static function downPresetOverridesFromSettings(): array
    {
        if (! self::systemSettingsAvailable()) {
            return [];
        }

        $overrides = [];

        try {
            foreach (self::DOWN_OPTION_KEYS as $key) {
                $value = SystemSettings::get("down_presets.{$key}");

                if ($value === null || $value === '') {
                    continue;
                }

                if (in_array($key, ['retry', 'refresh'], true) && is_numeric($value)) {
                    $overrides[$key] = (int) $value;

                    continue;
                }

                if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
                    $overrides[$key] = $value;
                }
            }
        } catch (\Throwable) {
            return [];
        }

        return $overrides;
    }

    /**
     * @param array<string, scalar> $base
     * @param array<string, scalar> $overrides
     * @return array<string, scalar>
     */
    private static function mergeDownOptions(array $base, array $overrides): array
    {
        if ($overrides === []) {
            return $base;
        }

        return self::normalizeOptions(array_merge($base, $overrides));
    }

    private static function systemSettingsAvailable(): bool
    {
        try {
            return class_exists(SystemSettings::class)
                && app()->bound('system.settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
