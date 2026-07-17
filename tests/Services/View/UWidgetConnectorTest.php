<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\View;

use Mockery;
use TestModules\TestModule\Repositories\ItemRepository;
use Unusualify\Modularous\Services\View\UWidget;
use Unusualify\Modularous\Tests\TestModulesCase;

class UWidgetConnectorTest extends TestModulesCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_init_connector_returns_repository_items_for_test_module(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([
                ['name' => 'First item'],
            ]));

        $this->app->instance(ItemRepository::class, $repository);

        $data = init_connector('TestModule:Item|repository:list');

        $this->assertArrayHasKey('items', $data);
        $this->assertSame('First item', $data['items']->first()['name']);
    }

    public function test_set_table_attributes_builds_ue_table_from_connector(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([
                ['name' => 'First item'],
                ['name' => 'Second item'],
            ]));

        $this->app->instance(ItemRepository::class, $repository);

        $widget = (new UWidget)->makeComponent('v-col');
        $setTableAttributes = \Closure::bind(function (array $attributes) {
            return $this->setTableAttributes($attributes);
        }, $widget, UWidget::class);

        $table = $setTableAttributes([
            'component' => 'ue-table',
            'connector' => 'TestModule:Item|repository:list',
            'attributes' => ['dense' => true],
        ]);

        $this->assertInstanceOf(UWidget::class, $table);
        $this->assertSame('ue-table', $table->render()['tag']);
        $this->assertSame('First item', $table->render()['attributes']['items'][0]['name']);
    }

    public function test_set_widget_attributes_attaches_table_child_for_ue_table_component(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([
                ['name' => 'First item'],
            ]));

        $this->app->instance(ItemRepository::class, $repository);

        $widget = (new UWidget)
            ->makeComponent('v-col')
            ->setAttributes([
                'component' => 'ue-table',
                'connector' => 'TestModule:Item|repository:list',
                'attributes' => ['dense' => true],
            ]);

        $rendered = $widget->render();

        $this->assertArrayHasKey('elements', $rendered);
        $this->assertSame('ue-table', $rendered['elements'][0]['tag']);
    }

    public function test_set_component_attributes_builds_generic_component_from_connector(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([
                ['name' => 'Card item'],
            ]));

        $this->app->instance(ItemRepository::class, $repository);

        $widget = (new UWidget)->makeComponent('v-col');
        $setComponentAttributes = \Closure::bind(function (array $attributes) {
            return $this->setComponentAttributes($attributes);
        }, $widget, UWidget::class);

        $component = $setComponentAttributes([
            'component' => 'ue-card',
            'connector' => 'TestModule:Item|repository:list',
            'attributes' => ['title' => 'Summary'],
        ]);

        $rendered = $component->render();

        $this->assertSame('ue-card', $rendered['tag']);
        $this->assertSame('Summary', $rendered['attributes']['title']);
        $this->assertSame('Card item', $rendered['attributes']['items'][0]['name']);
    }

    public function test_set_board_information_plus_attributes_enriches_cards_from_connector(): void
    {
        $repository = Mockery::mock(ItemRepository::class);
        $repository->shouldReceive('list')
            ->once()
            ->andReturn(collect([
                ['name' => 'Metric'],
            ]));

        $this->app->instance(ItemRepository::class, $repository);

        $widget = (new UWidget)->makeComponent('v-col');
        $setBoardAttributes = \Closure::bind(function (array $attributes) {
            return $this->setBoardInformationPlusAttributes($attributes);
        }, $widget, UWidget::class);

        $board = $setBoardAttributes([
            'component' => 'ue-board-information-plus',
            'cards' => [
                [
                    'connector' => 'TestModule:Item|repository:list',
                    'title' => 'Overview',
                ],
            ],
            'attributes' => [],
        ]);

        $rendered = $board->render();

        $this->assertSame('ue-board-information-plus', $rendered['tag']);
        $this->assertSame('Overview', $rendered['attributes']['cards'][0]['title']);
        $this->assertSame('Metric', $rendered['attributes']['cards'][0]['data']['items']->first()['name']);
    }
}
