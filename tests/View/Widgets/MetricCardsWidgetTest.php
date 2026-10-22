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

    public function test_render_marks_widget_col_as_auto_height_by_default()
    {
        $widget = new MetricCardsWidget;

        $this->assertTrue($widget->widgetAutoHeight);

        $result = $widget->render();

        $this->assertEquals(MetricCardsWidget::AUTO_HEIGHT_CLASS, $result['attributes']['class']);
    }

    public function test_render_appends_auto_height_class_to_widget_attributes_class_once()
    {
        $widget = (new MetricCardsWidget)
            ->setWidgetAttributes(['class' => 'd-flex min-height-0']);

        $this->assertEquals('d-flex min-height-0 ue-blocks__col--auto', $widget->render()['attributes']['class']);

        $widget->setWidgetAttributes(['class' => 'd-flex ue-blocks__col--auto']);

        $this->assertEquals('d-flex ue-blocks__col--auto', $widget->render()['attributes']['class']);
    }

    public function test_render_omits_auto_height_class_when_disabled()
    {
        $widget = (new MetricCardsWidget)
            ->setWidgetAttributes(['class' => 'd-flex'])
            ->setWidgetAutoHeight(false);

        $this->assertEquals('d-flex', $widget->render()['attributes']['class']);
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
