<?php

namespace Unusualify\Modularous\Tests\View;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Component;
use Unusualify\Modularous\View\ModularousWidget;
use Unusualify\Modularous\View\Widgets\MetricsWidget;

class ModularousWidgetTest extends TestCase
{
    public function test_widget_setters_return_self_for_fluent_interface()
    {
        $widget = new MetricsWidget;

        $this->assertSame($widget, $widget->setWidgetAlias('custom-alias'));
        $this->assertSame($widget, $widget->setWidgetCol(['cols' => 6]));
        $this->assertSame($widget, $widget->setWidgetAttributes(['class' => 'test']));
        $this->assertSame($widget, $widget->setWidgetSlots([]));
        $this->assertSame($widget, $widget->setWidgetDirectives(['scrollable' => false]));
        $this->assertSame($widget, $widget->useWidgetConfig(false));
    }

    public function test_hydrate_attributes_merges_with_defaults()
    {
        $widget = new MetricsWidget;
        $attributes = ['title' => 'Custom Title'];

        $result = $widget->hydrateAttributes($attributes);

        $this->assertArrayHasKey('title', $result);
        $this->assertEquals('Custom Title', $result['title']);
    }

    public function test_metrics_widget_has_correct_tag_from_alias()
    {
        $widget = new MetricsWidget;

        $this->assertEquals('ue-metrics', $widget->tag);
    }

    public function test_render_returns_widget_structure_with_tag_attributes_slots_elements()
    {
        $widget = new MetricsWidget;

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('v-col', $result['tag']);
        $this->assertSame(true, $result['directives']['scrollable']);
    }

    public function test_render_merges_widget_col_into_attributes()
    {
        $widget = new MetricsWidget;
        $widget->setWidgetCol(['cols' => 6, 'lg' => 4]);

        $result = $widget->render();

        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('cols', $result['attributes']);
        $this->assertEquals(6, $result['attributes']['cols']);
    }

    public function test_render_includes_widget_directives()
    {
        $widget = new MetricsWidget;
        $widget->setWidgetDirectives(['scrollable' => true, 'show' => false]);

        $result = $widget->render();

        $this->assertSame(true, $result['directives']['scrollable']);
        $this->assertSame(false, $result['directives']['show']);
    }

    public function test_render_uses_widget_config_when_enabled()
    {
        Config::set('modularous.widgets.metrics', [
            'attributes' => ['configTitle' => 'From Config'],
            'slots' => ['footer' => ['config slot']],
        ]);

        $widget = new MetricsWidget;
        $widget->useWidgetConfig(true);
        $widget->mergeAttributes(['title' => 'Override']);

        $result = $widget->render();

        $this->assertIsArray($result);
        $this->assertIsArray($result['elements']);
    }

    public function test_from_widget_template_throws_when_template_not_found()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widget template not found');

        MetricsWidget::fromWidgetTemplate('non-existent-template');
    }

    public function test_from_widget_template_returns_component_when_template_exists()
    {
        Config::set('modularous.widgets.test-metrics', [
            'tag' => 'v-col',
            'attributes' => ['title' => 'Test'],
        ]);

        $result = MetricsWidget::fromWidgetTemplate('test-metrics');

        $this->assertInstanceOf(MetricsWidget::class, $result);
    }

    public function test_extends_component_and_inherits_behavior()
    {
        $widget = new MetricsWidget;

        $this->assertInstanceOf(ModularousWidget::class, $widget);
        $this->assertInstanceOf(Component::class, $widget);
    }
}
