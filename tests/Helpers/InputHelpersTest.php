<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Support\Facades\Config;
use Unusualify\Modularous\Tests\TestCase;

class InputHelpersTest extends TestCase
{
    /** @test */
    public function test_configure_input_processes_input_array()
    {
        $input = [
            'name' => 'email',
            'type' => 'text',
        ];

        $result = configure_input($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
    }

    /** @test */
    public function test_modularous_default_input_returns_default_structure()
    {
        $result = modularous_default_input();

        $this->assertIsArray($result);
        // Default input should have standard keys
    }

    /** @test */
    public function test_hydrate_input_type_processes_type()
    {
        $input = ['type' => 'text'];

        $result = hydrate_input_type($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_hydrate_input_processes_full_input()
    {
        $input = [
            'name' => 'title',
            'type' => 'text',
        ];

        $result = hydrate_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_format_input_formats_input_data()
    {
        $input = [
            'name' => 'description',
            'type' => 'textarea',
        ];

        $result = format_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_format_input_wraps_format_input()
    {
        $input = [
            'name' => 'status',
            'type' => 'select',
        ];

        $result = modularous_format_input($input);

        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_format_inputs_processes_multiple_inputs()
    {
        $inputs = [
            ['name' => 'field1', 'type' => 'text'],
            ['name' => 'field2', 'type' => 'number'],
        ];

        $result = modularous_format_inputs($inputs);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /** @test */
    public function test_configure_input_maps_numeric_flags_and_keeps_named_keys(): void
    {
        $result = configure_input([
            'name' => 'title',
            'type' => 'text',
            0 => 'dense',
            1 => 'clearable',
        ]);

        $this->assertSame('title', $result['name']);
        $this->assertTrue($result['dense']);
        $this->assertTrue($result['clearable']);
    }

    /** @test */
    public function test_hydrate_input_type_merges_configured_type_aliases(): void
    {
        Config::set('modularous.input_types', [
            'email' => ['type' => 'text', 'inputType' => 'email', 'prependInnerIcon' => 'mdi-email'],
        ]);

        $result = hydrate_input_type(['type' => 'email', 'name' => 'email']);
        $this->assertSame('text', $result['type']);
        $this->assertSame('email', $result['inputType']);
        $this->assertSame('email', $result['name']);

        $passthrough = hydrate_input_type(['type' => 'custom', 'name' => 'x']);
        $this->assertSame('custom', $passthrough['type']);
    }

    /** @test */
    public function test_modularous_format_input_covers_title_divider_and_empty_name(): void
    {
        Config::set('modularous.default_input', [
            'type' => 'text',
            'color' => 'primary',
            'col' => ['cols' => 12],
        ]);

        $title = modularous_format_input(['type' => 'title', 'label' => 'Heading']);
        $this->assertCount(1, $title);
        $titleInput = array_values($title)[0];
        $this->assertSame('title', $titleInput['type']);
        $this->assertSame('a-0', $titleInput['padding']);

        $divider = modularous_format_input(['type' => 'divider']);
        $this->assertCount(1, $divider);

        $empty = modularous_format_input(['type' => 'text']);
        $this->assertSame([], $empty);
    }

    /** @test */
    public function test_modularous_format_inputs_skips_queries_in_console(): void
    {
        Config::set('modularous.default_input', [
            'type' => 'text',
            'color' => '',
            'col' => ['cols' => 12],
        ]);

        $formatted = modularous_format_inputs([
            ['type' => 'text', 'name' => 'title', 'ext' => 'date'],
            ['type' => 'text', 'name' => 'time_field', 'ext' => 'time'],
        ], null, null, true);

        $this->assertArrayHasKey('title', $formatted);
        $this->assertSame(date('Y-m-d'), $formatted['title']['default']);
        $this->assertSame(date('H:i'), $formatted['time_field']['default']);
    }
}
