<?php

namespace Unusualify\Modularous\Tests\View\Widgets;

use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Widgets\MetricCardsWidget;

class MetricCardsWidgetTest extends TestCase
{
    public function test_widget_can_be_instantiated()
    {
        $widget = new MetricCardsWidget;

        $this->assertInstanceOf(MetricCardsWidget::class, $widget);
        $this->assertEquals('ue-metric-cards', $widget->tag);
        $this->assertEquals('v-col', $widget->widgetTag);
        $this->assertEquals(12, $widget->widgetCol['lg']);
    }

    public function test_hydrate_attributes_returns_attributes_when_no_items()
    {
        $widget = new MetricCardsWidget;
        $attributes = ['title' => 'Test'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Test', $result['title']);
        $this->assertArrayHasKey('endpoint', $result);
    }

    public function test_hydrate_attributes_processes_items_without_connector()
    {
        $widget = new MetricCardsWidget;
        $attributes = [
            'items' => [
                ['label' => 'Metric', 'value' => 10],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('Metric', $result['items'][0]['label']);
        $this->assertEquals(10, $result['items'][0]['value']);
    }

    public function test_hydrate_attributes_executes_callable_value()
    {
        $widget = new MetricCardsWidget;
        $attributes = [
            'items' => [
                [
                    'label' => 'Callable metric',
                    'value' => fn () => 99,
                ],
            ],
        ];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertEquals(99, $result['items'][0]['value']);
    }

    public function test_hydrate_attributes_sets_endpoint()
    {
        $widget = new MetricCardsWidget;
        $attributes = ['title' => 'Test'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('endpoint', $result);
        $this->assertStringContainsString('metrics', $result['endpoint']);
    }

    public function test_render_returns_widget_structure()
    {
        $widget = new MetricCardsWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
        $this->assertIsArray($result['elements']);
        $this->assertEquals('ue-metric-cards', $result['elements'][0]['tag']);
    }

    public function test_default_attributes_include_metric_attributes()
    {
        $widget = new MetricCardsWidget;

        $this->assertArrayHasKey('metricAttributes', $widget->attributes);
        $this->assertArrayHasKey('color', $widget->attributes['metricAttributes']);
        $this->assertEquals('primary', $widget->attributes['metricAttributes']['color']);
        $this->assertEquals(2, $widget->attributes['metricAttributes']['padDigits']);
    }
}
