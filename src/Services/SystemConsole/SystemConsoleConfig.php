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
     *     key: string,
     *     command: string,
     *     label: string,
     *     tooltip: string|null,
     *     confirm: bool,
     *     color: string|null,
     *     variant: string|null,
     *     icon: string|null,
     *     arguments: array<string, scalar>,
     *     options: array<string, scalar>
     * }>
     */
    public static function cacheCommandsForUi(): array
    {
        return self::commandsForUi('system_console.cache_commands', uniqueBy: 'command');
    }

    /**
     * Extra one-click commands; uniqued by config key so the same artisan
     * command may appear more than once with different arguments/options.
     *
     * @return list<array{
     *     key: string,
     *     command: string,
     *     label: string,
     *     tooltip: string|null,
     *     confirm: bool,
     *     color: string|null,
     *     variant: string|null,
     *     icon: string|null,
     *     arguments: array<string, scalar>,
     *     options: array<string, scalar>
     * }>
     */
    public static function customCommandsForUi(): array
    {
        return self::commandsForUi('system_console.custom_commands', uniqueBy: 'key');
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
     * @param 'command'|'key' $uniqueBy
     * @return list<array{
     *     key: string,
     *     command: string,
     *     label: string,
     *     tooltip: string|null,
     *     confirm: bool,
     *     color: string|null,
     *     variant: string|null,
     *     icon: string|null,
     *     arguments: array<string, scalar>,
     *     options: array<string, scalar>
     * }>
     */
    private static function commandsForUi(string $configKey, string $uniqueBy = 'command'): array
    {
        /** @var array<int|string, mixed> $commands */
        $commands = (array) modularousConfig($configKey, []);

        $items = [];
        $seen = [];

        foreach ($commands as $index => $command) {
            if (! is_array($command) || empty($command['command'])) {
                continue;
            }

            $name = (string) $command['command'];
            $key = (string) ($command['key'] ?? (is_string($index) && $index !== '' ? $index : $name));
            $uniqueValue = $uniqueBy === 'key' ? $key : $name;

            if ($uniqueValue === '' || isset($seen[$uniqueValue])) {
                continue;
            }
            $seen[$uniqueValue] = true;

            $tooltip = isset($command['tooltip']) ? trim((string) $command['tooltip']) : '';

            $items[] = [
                'key' => $key !== '' ? $key : $name,
                'command' => $name,
                'label' => (string) ($command['label'] ?? $name),
                'tooltip' => $tooltip !== '' ? $tooltip : null,
                'confirm' => (bool) ($command['confirm'] ?? false),
                'color' => isset($command['color']) ? (string) $command['color'] : null,
                'variant' => isset($command['variant']) ? (string) $command['variant'] : 'tonal',
                'icon' => isset($command['icon']) ? (string) $command['icon'] : null,
                'arguments' => self::normalizeOptions((array) ($command['arguments'] ?? [])),
                'options' => self::normalizeOptions((array) ($command['options'] ?? [])),
            ];
        }

        return $items;
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
