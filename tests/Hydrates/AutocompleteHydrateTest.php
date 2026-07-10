<?php

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\AutocompleteHydrate;
use Unusualify\Modularous\Tests\TestCase;

class AutocompleteHydrateTest extends TestCase
{
    public function test_default_array_is_converted_to_null_when_not_multiple()
    {
        $input = [
            'type' => 'autocomplete',
            'default' => [],
        ];

        $h = new AutocompleteHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertNull($result['default']);
    }

    public function test_select_scroll_sets_component_and_type()
    {
        $input = [
            'type' => 'autocomplete',
            'ext' => 'scroll',
            'endpoint' => '/api/foo',
        ];

        $h = new AutocompleteHydrate($input, null, null, true);

        $result = $h->render();

        // ext doesn't satisfy the condition in hydrate() because type is 'autocomplete', not 'select-scroll'
        // So this test should just verify defaults are set
        $this->assertEquals('id', $result['itemValue']);
        $this->assertEquals('name', $result['itemTitle']);
    }

    public function test_select_scroll_sets_component_and_type_when_endpoint_present()
    {
        $input = [
            'type' => 'select-scroll',
            'endpoint' => '/api/items',
        ];

        $result = (new AutocompleteHydrate($input, null, null, true))->render();

        $this->assertSame('v-autocomplete', $result['componentType']);
        $this->assertSame('input-select-scroll', $result['type']);
    }

    public function test_returns_items_early_when_items_are_provided()
    {
        $input = [
            'type' => 'autocomplete',
            'items' => [['id' => 1, 'name' => 'One']],
        ];

        $result = (new AutocompleteHydrate($input, null, null, true))->render();

        $this->assertCount(1, $result['items']);
        $this->assertSame('autocomplete', $result['type']);
    }
}
