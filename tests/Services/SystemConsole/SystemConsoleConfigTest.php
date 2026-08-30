<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\SystemConsole;

use Unusualify\Modularous\Services\SystemConsole\SystemConsoleConfig;
use Unusualify\Modularous\Tests\TestCase;

class SystemConsoleConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'modularous.system_console.down_presets' => [
                'default' => [
                    'label' => 'Default maintenance',
                    'options' => [
                        'render' => 'modularous::maintenance',
                        'retry' => 60,
                        'refresh' => 30,
                        'secret' => 'test-secret',
                    ],
                ],
                'minimal' => [
                    'label' => 'Minimal',
                    'options' => [
                        'retry' => 15,
                        'secret' => '',
                        'redirect' => null,
                    ],
                ],
            ],
            'modularous.system_console.default_down_preset' => 'default',
            'modularous.system_console.cache_commands' => [
                [
                    'command' => 'route:clear',
                    'label' => 'Route Clear',
                    'confirm' => false,
                    'color' => 'warning',
                    'icon' => 'mdi-routes',
                ],
                [
                    'command' => 'optimize',
                    'label' => 'Optimize',
                    'confirm' => true,
                ],
                [
                    'label' => 'Missing command',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_maps_down_presets_and_omits_empty_options(): void
    {
        $presets = SystemConsoleConfig::downPresetsForUi();

        $this->assertCount(2, $presets);
        $this->assertSame('default', $presets[0]['key']);
        $this->assertSame('Default maintenance', $presets[0]['label']);
        $this->assertSame([
            'render' => 'modularous::maintenance',
            'retry' => 60,
            'refresh' => 30,
            'secret' => 'test-secret',
        ], $presets[0]['options']);

        $this->assertSame('minimal', $presets[1]['key']);
        $this->assertSame(['retry' => 15], $presets[1]['options']);
    }

    /** @test */
    public function it_resolves_default_down_preset_key(): void
    {
        $this->assertSame('default', SystemConsoleConfig::defaultDownPresetKey());

        config(['modularous.system_console.default_down_preset' => 'missing']);
        $this->assertSame('default', SystemConsoleConfig::defaultDownPresetKey());
    }

    /** @test */
    public function it_uses_config_defaults_when_system_settings_unavailable(): void
    {
        $this->assertFalse(app()->bound('system.settings'));

        $presets = SystemConsoleConfig::downPresetsForUi();

        $this->assertSame([
            'render' => 'modularous::maintenance',
            'retry' => 60,
            'refresh' => 30,
            'secret' => 'test-secret',
        ], $presets[0]['options']);
        $this->assertSame([], SystemConsoleConfig::downPresetOverridesFromSettings());
    }

    /** @test */
    public function it_overrides_default_preset_options_from_system_settings(): void
    {
        $this->bindSystemSettings([
            'down_presets' => [
                'render' => 'custom::maintenance',
                'retry' => 120,
                'refresh' => 45,
                'secret' => 'settings-secret',
            ],
        ]);

        $presets = SystemConsoleConfig::downPresetsForUi();

        $this->assertSame([
            'render' => 'custom::maintenance',
            'retry' => 120,
            'refresh' => 45,
            'secret' => 'settings-secret',
        ], $presets[0]['options']);

        // Non-default presets remain config-only.
        $this->assertSame(['retry' => 15], $presets[1]['options']);
    }

    /** @test */
    public function it_merges_partial_system_settings_overrides_onto_config_defaults(): void
    {
        $this->bindSystemSettings([
            'down_presets' => [
                'retry' => 90,
                'secret' => '',
                'render' => null,
            ],
        ]);

        $presets = SystemConsoleConfig::downPresetsForUi();

        $this->assertSame([
            'render' => 'modularous::maintenance',
            'retry' => 90,
            'refresh' => 30,
            'secret' => 'test-secret',
        ], $presets[0]['options']);
    }

    /** @test */
    public function it_maps_cache_commands_and_skips_invalid_entries(): void
    {
        $commands = SystemConsoleConfig::cacheCommandsForUi();

        $this->assertCount(2, $commands);
        $this->assertSame('route:clear', $commands[0]['command']);
        $this->assertSame('route:clear', $commands[0]['key']);
        $this->assertFalse($commands[0]['confirm']);
        $this->assertSame('warning', $commands[0]['color']);
        $this->assertSame('tonal', $commands[0]['variant']);
        $this->assertNull($commands[0]['tooltip']);
        $this->assertSame([], $commands[0]['arguments']);
        $this->assertSame([], $commands[0]['options']);

        $this->assertSame('optimize', $commands[1]['command']);
        $this->assertTrue($commands[1]['confirm']);
        $this->assertNull($commands[1]['color']);
    }

    /** @test */
    public function it_maps_custom_commands_with_tooltip_arguments_and_options(): void
    {
        config([
            'modularous.system_console.custom_commands' => [
                'queue-restart' => [
                    'command' => 'queue:restart',
                    'label' => 'Restart queues',
                    'tooltip' => 'Signals workers to restart.',
                    'confirm' => true,
                    'color' => 'secondary',
                    'icon' => 'mdi-restart',
                ],
                'inspire-verbose' => [
                    'command' => 'inspire',
                    'label' => 'Inspire',
                    'tooltip' => '  Print a quote.  ',
                    'arguments' => [
                        'name' => '',
                    ],
                    'options' => [
                        'force' => true,
                        'empty' => null,
                    ],
                ],
                [
                    'label' => 'Missing command',
                ],
            ],
        ]);

        $commands = SystemConsoleConfig::customCommandsForUi();

        $this->assertCount(2, $commands);
        $this->assertSame('queue-restart', $commands[0]['key']);
        $this->assertSame('queue:restart', $commands[0]['command']);
        $this->assertSame('Restart queues', $commands[0]['label']);
        $this->assertSame('Signals workers to restart.', $commands[0]['tooltip']);
        $this->assertTrue($commands[0]['confirm']);
        $this->assertSame('secondary', $commands[0]['color']);
        $this->assertSame('mdi-restart', $commands[0]['icon']);

        $this->assertSame('inspire-verbose', $commands[1]['key']);
        $this->assertSame('inspire', $commands[1]['command']);
        $this->assertSame('Print a quote.', $commands[1]['tooltip']);
        $this->assertSame(['force' => true], $commands[1]['options']);
        $this->assertSame([], $commands[1]['arguments']);
    }

    /** @test */
    public function it_deduplicates_custom_commands_by_key_not_command_name(): void
    {
        config([
            'modularous.system_console.custom_commands' => [
                'inspire-a' => [
                    'command' => 'inspire',
                    'label' => 'Inspire A',
                    'options' => ['force' => true],
                ],
                'inspire-b' => [
                    'command' => 'inspire',
                    'label' => 'Inspire B',
                ],
                'inspire-a-dup' => [
                    'key' => 'inspire-a',
                    'command' => 'inspire',
                    'label' => 'Inspire duplicate key',
                ],
            ],
        ]);

        $commands = SystemConsoleConfig::customCommandsForUi();

        $this->assertCount(2, $commands);
        $this->assertSame(['inspire-a', 'inspire-b'], array_column($commands, 'key'));
        $this->assertSame(['inspire', 'inspire'], array_column($commands, 'command'));
        $this->assertSame('Inspire A', $commands[0]['label']);
    }

    /** @test */
    public function it_returns_empty_custom_commands_when_unconfigured(): void
    {
        config(['modularous.system_console.custom_commands' => []]);

        $this->assertSame([], SystemConsoleConfig::customCommandsForUi());
    }

    /** @test */
    public function it_deduplicates_cache_commands_by_command_name(): void
    {
        config([
            'modularous.system_console.cache_commands' => [
                [
                    'command' => 'route:clear',
                    'label' => 'Route Clear',
                ],
                [
                    'command' => 'optimize',
                    'label' => 'Optimize A',
                ],
                [
                    'command' => 'route:clear',
                    'label' => 'Route Clear Duplicate',
                ],
                [
                    'command' => 'optimize',
                    'label' => 'Optimize B',
                ],
            ],
        ]);

        $commands = SystemConsoleConfig::cacheCommandsForUi();

        $this->assertCount(2, $commands);
        $this->assertSame(['route:clear', 'optimize'], array_column($commands, 'command'));
        $this->assertSame('Optimize A', $commands[1]['label']);
    }

    /** @test */
    public function it_normalizes_options(): void
    {
        $this->assertSame(
            ['retry' => 60, 'enabled' => true],
            SystemConsoleConfig::normalizeOptions([
                'retry' => 60,
                'secret' => '',
                'redirect' => null,
                'enabled' => true,
                'nested' => ['nope'],
            ])
        );
    }

    /** @test */
    public function it_reports_maintenance_status(): void
    {
        $this->assertIsBool(SystemConsoleConfig::isInMaintenance());
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function bindSystemSettings(array $snapshot): void
    {
        $this->app->instance('system.settings', new class($snapshot)
        {
            /** @param array<string, mixed> $snapshot */
            public function __construct(private array $snapshot) {}

            public function get(string $key, mixed $default = null, ?string $locale = null): mixed
            {
                $segments = $key === '' ? [] : explode('.', $key);
                $cursor = $this->snapshot;

                foreach ($segments as $segment) {
                    if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                        return $default;
                    }
                    $cursor = $cursor[$segment];
                }

                return $cursor;
            }
        });
    }
}
