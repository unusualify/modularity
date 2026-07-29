<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\View\Widgets;

use Mockery;
use TestModules\TestModule\Repositories\ItemRepository;
use Unusualify\Modularous\Tests\TestModulesCase;
use Unusualify\Modularous\View\Widgets\BoardInformationWidget;
use Unusualify\Modularous\View\Widgets\MetricGroupsWidget;
use Unusualify\Modularous\View\Widgets\MetricsWidget;

/**
 * Connector branches for dashboard widgets — uses TestModule Item repository
 * as the minimal connector target (same pattern as UWidgetConnectorTest).
 */
class WidgetConnectorTest extends TestModulesCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function metrics_widget_hydrates_connector_value_and_push_events(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(42);
        $repository->shouldReceive('count')
            ->once()
            ->andReturn(7);

        $this->app->instance(ItemRepository::class, $repository);

        $result = (new MetricsWidget)->hydrateAttributes([
            'items' => [
                [
                    'title' => 'Listed',
                    'connector' => 'TestModule|Item^repository->list',
                ],
                [
                    'title' => 'Counted',
                    'connector' => 'TestModule|Item^repository',
                    'pushEvents' => [
                        ['name' => 'count', 'args' => []],
                    ],
                ],
            ],
        ]);

        $this->assertSame(42, $result['items'][0]['value']);
        $this->assertSame(7, $result['items'][1]['value']);
        $this->assertArrayHasKey('endpoint', $result);
    }

    /** @test */
    public function board_information_widget_hydrates_cards_from_connector(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([['name' => 'Board metric']]));

        $this->app->instance(ItemRepository::class, $repository);

        $result = (new BoardInformationWidget)->hydrateAttributes([
            'cards' => [
                [
                    'title' => 'Overview',
                    'connector' => 'TestModule:Item|repository:list',
                ],
            ],
        ]);

        $this->assertCount(1, $result['cards']);
        $this->assertSame('Overview', $result['cards'][0]['title']);
        $this->assertSame('Board metric', $result['cards'][0]['data']['items']->first()['name']);
    }

    /** @test */
    public function metric_groups_widget_hydrates_nested_connector_values(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(99);

        $this->app->instance(ItemRepository::class, $repository);

        $result = (new MetricGroupsWidget)->hydrateAttributes([
            'items' => [
                [
                    'title' => 'Group',
                    'items' => [
                        [
                            'title' => 'Nested metric',
                            'connector' => 'TestModule|Item^repository->list',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame(99, $result['items'][0]['items'][0]['value']);
    }
}
