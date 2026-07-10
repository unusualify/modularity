<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\View;

use BadMethodCallException;
use Unusualify\Modularous\Services\View\UComponent;
use Unusualify\Modularous\Tests\TestCase;

class UComponentTest extends TestCase
{
    public function test_make_component_builds_renderable_structure(): void
    {
        $component = UComponent::make()
            ->makeComponent('v-btn', ['color' => 'primary'], 'Click me', ['default' => 'slot'], ['if' => true]);

        $rendered = $component->render();

        $this->assertSame('v-btn', $rendered['tag']);
        $this->assertSame(['color' => 'primary'], $rendered['attributes']);
        $this->assertSame(['default' => 'slot'], $rendered['slots']);
        $this->assertSame(['if' => true], $rendered['directives']);
        $this->assertSame('Click me', $rendered['elements']);
    }

    public function test_add_children_appends_string_and_component_children(): void
    {
        $child = UComponent::make()->makeComponent('v-icon', ['icon' => 'mdi-check']);

        $component = UComponent::make()
            ->makeComponent('v-card')
            ->addChildren('Title')
            ->addChildren($child);

        $rendered = $component->render();

        $this->assertCount(2, $rendered['elements']);
        $this->assertSame($child->render(), $rendered['elements'][1]);
    }

    public function test_magic_make_methods_create_kebab_case_tags(): void
    {
        $component = UComponent::makeVBtn(['flat' => true]);

        $this->assertSame('v-btn', $component->render()['tag']);
        $this->assertSame(['flat' => true], $component->render()['attributes']);
    }

    public function test_add_slot_stores_named_slot_content(): void
    {
        $component = UComponent::make()
            ->makeComponent('v-card')
            ->addSlot('actions', ['type' => 'submit']);

        $this->assertSame(['actions' => ['type' => 'submit']], $component->render()['slots']);
    }

    public function test_undefined_magic_method_throws_bad_method_call_exception(): void
    {
        $this->expectException(BadMethodCallException::class);

        UComponent::make()->unknownFactoryMethod();
    }
}
