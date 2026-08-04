<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\View\Widgets;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Mockery;
use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Widgets\ArtisanRunnerWidget;

class ArtisanRunnerWidgetTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function widget_can_be_instantiated(): void
    {
        $widget = new ArtisanRunnerWidget;

        $this->assertInstanceOf(ArtisanRunnerWidget::class, $widget);
        $this->assertSame('ue-artisan-runner', $widget->tag);
        $this->assertSame('v-col', $widget->widgetTag);
    }

    /** @test */
    public function hydrate_attributes_disables_runner_without_authenticated_user(): void
    {
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->never();
        $runner->shouldReceive('emptyPanelEndpoints')->andReturn([
            'commands' => '',
            'definition' => '',
            'run' => '',
            'answer' => '',
        ]);

        $this->app->instance(ArtisanRunnerInterface::class, $runner);

        $result = (new ArtisanRunnerWidget)->hydrateAttributes(['title' => 'Artisan Runner']);

        $this->assertTrue($result['runnerDisabled']);
        $this->assertSame('', $result['endpoints']['commands']);
        $this->assertFalse($result['isSuperadmin']);
    }

    /** @test */
    public function hydrate_attributes_enables_runner_for_allowed_user(): void
    {
        $user = new class extends Authenticatable
        {
            public bool $is_superadmin = true;

            public function getAuthIdentifier()
            {
                return 1;
            }
        };

        $this->actingAs($user);

        $endpoints = [
            'commands' => '/admin/artisan-runner/commands',
            'definition' => '/admin/artisan-runner/commands/__NAME__',
            'run' => '/admin/artisan-runner/runs',
            'answer' => '/admin/artisan-runner/runs/__RUN_ID__/answer',
        ];

        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->with($user)->andReturn(true);
        $runner->shouldReceive('panelEndpoints')->andReturn($endpoints);
        $runner->shouldReceive('emptyPanelEndpoints')->never();

        $this->app->instance(ArtisanRunnerInterface::class, $runner);

        $result = (new ArtisanRunnerWidget)->hydrateAttributes(['title' => 'Artisan Runner']);

        $this->assertFalse($result['runnerDisabled']);
        $this->assertSame($endpoints, $result['endpoints']);
        $this->assertTrue($result['isSuperadmin']);
    }

    /** @test */
    public function hydrate_attributes_disables_runner_when_user_cannot_access(): void
    {
        $user = new class extends Authenticatable
        {
            public bool $is_superadmin = false;

            public function getAuthIdentifier()
            {
                return 2;
            }
        };

        $this->actingAs($user);

        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('userCanAccess')->with($user)->andReturn(false);
        $runner->shouldReceive('emptyPanelEndpoints')->andReturn([
            'commands' => '',
            'definition' => '',
            'run' => '',
            'answer' => '',
        ]);
        $runner->shouldReceive('panelEndpoints')->never();

        $this->app->instance(ArtisanRunnerInterface::class, $runner);

        $result = (new ArtisanRunnerWidget)->hydrateAttributes([]);

        $this->assertTrue($result['runnerDisabled']);
        $this->assertFalse($result['isSuperadmin']);
    }

    /** @test */
    public function render_returns_widget_structure(): void
    {
        $runner = Mockery::mock(ArtisanRunnerInterface::class);
        $runner->shouldReceive('emptyPanelEndpoints')->andReturn([
            'commands' => '',
            'definition' => '',
            'run' => '',
            'answer' => '',
        ]);
        $this->app->instance(ArtisanRunnerInterface::class, $runner);

        $result = (new ArtisanRunnerWidget)->render();

        $this->assertIsArray($result);
        $this->assertSame('v-col', $result['tag']);
        $this->assertArrayHasKey('elements', $result);
    }
}
