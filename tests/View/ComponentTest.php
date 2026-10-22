<?php

namespace Unusualify\Modularous\Tests\View;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\TestCase;
use Unusualify\Modularous\View\Component;

class ComponentTest extends TestCase
{
    public function test_component_can_be_instantiated()
    {
        $component = new Component;

        $this->assertInstanceOf(Component::class, $component);
    }

    public function test_make_returns_new_instance()
    {
        $component = Component::make();

        $this->assertInstanceOf(Component::class, $component);
    }

    public function test_make_component_sets_tag_and_attributes()
    {
        $component = Component::make();

        $result = $component->makeComponent('v-btn', ['color' => 'primary'], 'Click me');

        $this->assertSame($component, $result);
        $this->assertEquals('v-btn', $component->tag);
        $this->assertEquals('primary', $component->attributes['color']);
    }

    public function test_set_tag_returns_self()
    {
        $component = Component::make();

        $result = $component->setTag('div');

        $this->assertSame($component, $result);
        $this->assertEquals('div', $component->tag);
    }

    public function test_set_attributes_hydrates_and_sets()
    {
        $component = Component::make();

        $result = $component->setAttributes(['class' => 'test-class']);

        $this->assertSame($component, $result);
        $this->assertEquals('test-class', $component->attributes['class']);
    }

    public function test_merge_attributes_merges_with_existing()
    {
        $component = Component::make();
        $component->setAttributes(['class' => 'base']);

        $component->mergeAttributes(['class' => 'added', 'id' => 'test']);

        $this->assertArrayHasKey('class', $component->attributes);
        $this->assertArrayHasKey('id', $component->attributes);
    }

    public function test_set_slots_and_merge_slots()
    {
        $component = Component::make();

        $component->setSlots(['default' => ['content']]);
        $component->mergeSlots(['footer' => ['footer content']]);

        $this->assertArrayHasKey('default', $component->slots);
        $this->assertArrayHasKey('footer', $component->slots);
    }

    public function test_add_directive()
    {
        $component = Component::make();

        $result = $component->addDirective('ripple')
            ->addDirective('html', '<strong>Bold</strong>');

        $this->assertSame($component, $result);
        $this->assertTrue($component->directives['ripple']);
        $this->assertEquals('<strong>Bold</strong>', $component->directives['html']);
    }

    public function test_set_elements()
    {
        $component = Component::make();

        $component->setElements('Child content');

        $this->assertEquals('Child content', $component->elements);
    }

    public function test_add_children_with_string()
    {
        $component = Component::make();

        $component->addChildren('First child');

        $this->assertIsArray($component->elements);
        $this->assertCount(1, $component->elements);
    }

    public function test_add_children_with_array()
    {
        $component = Component::make();

        $component->addChildren(['tag' => 'span', 'content' => 'child']);

        $this->assertIsArray($component->elements);
        $this->assertEquals(['tag' => 'span', 'content' => 'child'], $component->elements[0]);
    }

    public function test_add_slot()
    {
        $component = Component::make();

        $component->addSlot('default', ['slot content']);

        $this->assertEquals(['slot content'], $component->slots['default']);
    }

    public function test_render_returns_array_structure()
    {
        $component = Component::make();
        $component->setTag('div')
            ->setAttributes(['class' => 'test'])
            ->setElements('content');

        $result = $component->render();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('attributes', $result);
        $this->assertArrayHasKey('slots', $result);
        $this->assertArrayHasKey('directives', $result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertEquals('div', $result['tag']);
    }

    public function test_to_array_returns_render_output()
    {
        $component = Component::make()->setTag('span');

        $this->assertEquals($component->render(), $component->toArray());
    }

    public function test_to_json_returns_json_string()
    {
        $component = Component::make()->setTag('span')->setAttributes(['id' => 'test']);

        $json = $component->toJson();

        $this->assertIsString($json);
        $this->assertStringContainsString('span', $json);
    }

    public function test_create_with_tag_returns_array()
    {
        $result = Component::create([
            'tag' => 'v-card',
            'attributes' => ['title' => 'Test'],
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('v-card', $result['tag']);
        $this->assertEquals('Test', $result['attributes']['title']);
    }

    public function test_create_with_component_returns_array()
    {
        $result = Component::create([
            'component' => 'ue-card',
            'attributes' => ['title' => 'Card Title'],
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('ue-card', $result['tag']);
    }

    public function test_set_component_sets_tag()
    {
        $component = Component::make();

        $result = $component->setComponent('ue-button');

        $this->assertSame($component, $result);
        $this->assertEquals('ue-button', $component->tag);
    }

    public function test_create_throws_when_no_tag_widget_or_component()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widget, component or tag is required');

        Component::create(['attributes' => []]);
    }

    public function test_hydrate_attributes_resolves_href()
    {
        $component = Component::make();

        $result = $component->hydrateAttributes(['href' => 'admin.dashboard']);

        $this->assertArrayHasKey('href', $result);
    }

    public function test_make_div_via_magic_method()
    {
        $component = Component::make();

        $result = $component->makeDiv(['class' => 'container']);

        $this->assertInstanceOf(Component::class, $result);
        $this->assertEquals('div', $component->tag);
    }

    public function test_add_children_div_via_magic_method()
    {
        $component = Component::make();

        $result = $component->addChildrenDiv(['class' => 'child']);

        $this->assertIsArray($component->elements);
        $this->assertCount(1, $component->elements);
    }

    public function test_throws_bad_method_call_for_undefined_method()
    {
        $component = Component::make();

        $this->expectException(\BadMethodCallException::class);

        $component->nonExistentMethod();
    }

    public function test_set_elements_ignores_empty_string()
    {
        $component = Component::make();
        $component->setElements('content');

        $component->setElements('');

        $this->assertEquals('content', $component->elements);
    }

    public function test_add_children_appends_to_existing_elements()
    {
        $component = Component::make();
        $component->addChildren('First');
        $component->addChildren('Second');

        $this->assertCount(2, $component->elements);
    }

    public function test_add_children_with_child_component_renders_nested()
    {
        $child = new class extends Component
        {
            public function __construct()
            {
                parent::__construct();
                $this->setTag('span')->setAttributes(['class' => 'child']);
            }
        };

        $component = Component::make();
        $component->addChildren($child);

        $this->assertIsArray($component->elements);
        $this->assertCount(1, $component->elements);
        $this->assertIsArray($component->elements[0]);
        $this->assertEquals('span', $component->elements[0]['tag']);
    }

    public function test_render_omits_elements_key_when_null()
    {
        $component = Component::make();
        $component->setTag('div');

        $result = $component->render();

        $this->assertArrayNotHasKey('elements', $result);
    }

    public function test_merge_directives()
    {
        $component = Component::make();
        $component->addDirective('a', 1);
        $component->mergeDirectives(['b' => 2]);

        $this->assertEquals(1, $component->directives['a']);
        $this->assertEquals(2, $component->directives['b']);
    }

    public function test_make_component_with_slots_and_directives()
    {
        $component = Component::make();

        $result = $component->makeComponent(
            'v-card',
            ['title' => 'Card'],
            'body',
            ['footer' => ['Footer']],
            ['ripple' => true]
        );

        $this->assertSame($component, $result);
        $this->assertEquals('v-card', $component->tag);
        $this->assertEquals('body', $component->elements);
        $this->assertEquals(['footer' => ['Footer']], $component->slots);
        $this->assertTrue($component->directives['ripple']);
    }

    public function test_create_with_widget_returns_widget_render()
    {
        $result = Component::create([
            'widget' => 'MetricsWidget',
            'attributes' => ['title' => 'Dashboard Metrics'],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tag', $result);
        $this->assertArrayHasKey('elements', $result);
    }

    public function test_create_with_widget_auto_height_appends_blocks_auto_class()
    {
        $result = Component::create([
            'widget' => 'MetricsWidget',
            'widgetAutoHeight' => true,
            'widgetAttributes' => ['class' => 'd-flex flex-column'],
            'attributes' => ['title' => 'Dashboard Metrics'],
        ]);

        $this->assertEquals('d-flex flex-column ue-blocks__col--auto', $result['attributes']['class']);
    }

    public function test_create_with_widget_auto_height_false_overrides_widget_default()
    {
        $result = Component::create([
            'widget' => 'MetricCardsWidget',
            'widgetAutoHeight' => false,
            'widgetAttributes' => ['class' => 'd-flex'],
            'attributes' => ['title' => 'Glance'],
        ]);

        $this->assertEquals('d-flex', $result['attributes']['class']);
    }

    public function test_create_with_widget_throws_when_widget_class_missing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widget class');

        Component::create([
            'widget' => 'NonExistentWidget',
            'attributes' => [],
        ]);
    }

    public function test_create_with_widget_alias_merges_config()
    {
        Config::set('modularous.widgets.custom-metrics', [
            'tag' => 'v-col',
            'attributes' => ['fromConfig' => true],
        ]);

        $result = Component::create([
            'widgetAlias' => 'custom-metrics',
            'tag' => 'v-col',
            'attributes' => ['title' => 'Override'],
        ]);

        $this->assertIsArray($result);
    }

    public function test_to_string_returns_json()
    {
        $component = Component::make()->setTag('span')->setAttributes(['id' => 'x']);

        $str = (string) $component;

        $this->assertIsString($str);
        $this->assertStringContainsString('span', $str);
    }

    public function test_make_v_btn_via_magic_method()
    {
        $component = Component::make();

        $result = $component->makeVBtn(['color' => 'primary']);

        $this->assertInstanceOf(Component::class, $result);
        $this->assertEquals('v-btn', $component->tag);
    }

    public function test_make_ue_card_via_magic_method()
    {
        $component = Component::make();

        $result = $component->makeUeCard(['title' => 'Card']);

        $this->assertEquals('ue-card', $component->tag);
    }

    public function test_make_div_static_via_magic_method()
    {
        $result = Component::makeDiv(['class' => 'container']);

        $this->assertInstanceOf(Component::class, $result);
        $this->assertEquals('div', $result->tag);
    }

    public function test_add_children_span_via_magic_method_includes_tag()
    {
        $component = Component::make();

        $component->addChildrenSpan(['class' => 'text']);

        $this->assertIsArray($component->elements);
        $this->assertArrayHasKey('tag', $component->elements[0]);
        $this->assertEquals('span', $component->elements[0]['tag']);
    }

    public function test_add_children_with_empty_old_elements_creates_array()
    {
        $component = Component::make();
        $component->elements = null;

        $component->addChildren(['tag' => 'p']);

        $this->assertIsArray($component->elements);
        $this->assertCount(1, $component->elements);
    }

    public function test_add_children_with_single_string_element_creates_array()
    {
        $component = Component::make();
        $component->elements = 'single';

        $component->addChildren('another');

        $this->assertIsArray($component->elements);
        $this->assertCount(2, $component->elements);
    }
}
