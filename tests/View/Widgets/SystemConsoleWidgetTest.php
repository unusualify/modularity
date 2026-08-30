<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\View\Widgets;

use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Component;
use Unusualify\Modularous\View\Widgets\SystemConsoleWidget;

class SystemConsoleWidgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'modularous.artisan_runner.enabled' => true,
            'modularous.artisan_runner.allowed_roles' => ['superadmin'],
            'modularous.system_console.down_presets' => [
                'default' => [
                    'label' => 'Default maintenance',
                    'options' => [
                        'render' => 'modularous::maintenance',
                        'retry' => 60,
                        'secret' => 'secret',
                    ],
                ],
            ],
            'modularous.system_console.default_down_preset' => 'default',
            'modularous.system_console.cache_commands' => [
                [
                    'command' => 'optimize:clear',
                    'label' => 'Optimize Clear',
                    'confirm' => true,
                ],
            ],
        ]);
    }

    /** @test */
    public function widget_can_be_instantiated(): void
    {
        $widget = new SystemConsoleWidget;

        $this->assertInstanceOf(SystemConsoleWidget::class, $widget);
        $this->assertSame('ue-system-console', $widget->tag);
        $this->assertSame('v-col', $widget->widgetTag);
    }

    /** @test */
    public function hydrate_attributes_includes_console_payload_and_endpoints_shape(): void
    {
        $widget = new SystemConsoleWidget;
        $result = $widget->hydrateAttributes([
            'title' => 'System Console',
        ]);

        $this->assertSame('System Console', $result['title']);
        $this->assertTrue($result['runnerDisabled']);
        $this->assertIsArray($result['endpoints']);
        $this->assertArrayHasKey('commands', $result['endpoints']);
        $this->assertArrayHasKey('run', $result['endpoints']);
        $this->assertArrayHasKey('maintenanceMode', $result);
        $this->assertIsBool($result['maintenanceMode']);
        $this->assertSame('default', $result['defaultDownPreset']);
        $this->assertCount(1, $result['downPresets']);
        $this->assertSame('optimize:clear', $result['cacheCommands'][0]['command']);
        $this->assertArrayHasKey('customCommands', $result);
        $this->assertSame([], $result['customCommands']);
    }

    /** @test */
    public function use_widget_config_render_does_not_duplicate_cache_commands(): void
    {
        config([
            'modularous.system_console.cache_commands' => [
                'route:clear' => [
                    'command' => 'route:clear',
                    'label' => 'Route Clear',
                ],
                'optimize' => [
                    'command' => 'optimize',
                    'label' => 'Optimize',
                    'confirm' => true,
                ],
            ],
            'modularous.system_console.custom_commands' => [
                'queue-restart' => [
                    'command' => 'queue:restart',
                    'label' => 'Restart queues',
                    'tooltip' => 'Signals workers to restart.',
                ],
            ],
            'modularous.widgets.system-console' => [
                'attributes' => [],
                'slots' => [],
            ],
        ]);

        $widget = new SystemConsoleWidget;
        $widget->useWidgetConfig(true);
        $widget->mergeAttributes([
            'title' => 'System Console',
            'subtitle' => 'Maintenance mode and cache / optimize commands.',
        ]);

        $result = $widget->render();
        $inner = $result['elements'][0] ?? null;

        $this->assertIsArray($inner);
        $this->assertArrayHasKey('attributes', $inner);

        $cacheCommands = $inner['attributes']['cacheCommands'] ?? [];
        $downPresets = $inner['attributes']['downPresets'] ?? [];

        $this->assertCount(2, $cacheCommands);
        $this->assertSame(['route:clear', 'optimize'], array_column($cacheCommands, 'command'));
        $this->assertCount(1, $downPresets);
        $this->assertSame(['default'], array_column($downPresets, 'key'));

        $customCommands = $inner['attributes']['customCommands'] ?? [];
        $this->assertCount(1, $customCommands);
        $this->assertSame('queue-restart', $customCommands[0]['key']);
        $this->assertSame('Signals workers to restart.', $customCommands[0]['tooltip']);
    }

    /** @test */
    public function component_create_dashboard_block_does_not_duplicate_lists(): void
    {
        config([
            'modularous.system_console.cache_commands' => [
                'route:clear' => [
                    'command' => 'route:clear',
                    'label' => 'Route Clear',
                ],
                'route:cache' => [
                    'command' => 'route:cache',
                    'label' => 'Route Cache',
                ],
                'optimize:clear' => [
                    'command' => 'optimize:clear',
                    'label' => 'Optimize Clear',
                ],
                'optimize' => [
                    'command' => 'optimize',
                    'label' => 'Optimize',
                ],
            ],
            'modularous.system_console.custom_commands' => [
                'queue-restart' => [
                    'command' => 'queue:restart',
                    'label' => 'Restart queues',
                    'tooltip' => 'Signals workers to restart.',
                ],
                'inspire' => [
                    'command' => 'inspire',
                    'label' => 'Inspire',
                ],
            ],
        ]);

        $rendered = Component::create([
            'widget' => 'SystemConsoleWidget',
            'widgetCol' => [
                'cols' => 12,
                'lg' => 6,
            ],
            'attributes' => [
                'title' => 'System Console',
                'subtitle' => 'Maintenance mode and cache / optimize commands.',
                'elevation' => 2,
            ],
        ]);

        $inner = $rendered['elements'][0] ?? null;
        $this->assertIsArray($inner);

        $cacheCommands = $inner['attributes']['cacheCommands'] ?? [];
        $this->assertCount(4, $cacheCommands);
        $this->assertSame(
            ['route:clear', 'route:cache', 'optimize:clear', 'optimize'],
            array_column($cacheCommands, 'command')
        );

        $customCommands = $inner['attributes']['customCommands'] ?? [];
        $this->assertCount(2, $customCommands);
        $this->assertSame(['queue-restart', 'inspire'], array_column($customCommands, 'key'));
        $this->assertSame('Signals workers to restart.', $customCommands[0]['tooltip']);
    }

    /** @test */
    public function render_returns_widget_structure(): void
    {
        $widget = new SystemConsoleWidget;
        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertSame('v-col', $result['tag']);
        $this->assertArrayHasKey('elements', $result);
        $this->assertIsArray($result['elements']);
    }
}
