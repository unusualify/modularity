<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\FormEventCompiler;
use Unusualify\Modularous\Tests\TestCase;

class FormEventCompilerTest extends TestCase
{
    /** @test */
    public function it_compiles_form_events_key_and_dual_writes_schema(): void
    {
        $input = [
            'type' => 'text',
            'name' => 'title',
            'formEvents' => 'update:slugs:slugSourceValue:modelValue:fallback',
        ];
        $data = null;
        $arrayable = false;

        FormEventCompiler::apply($input, $data, $arrayable, []);

        $this->assertSame(
            ['formatUpdate:slugs:slugSourceValue:modelValue:fallback'],
            $input['formEvents']
        );
        $this->assertSame(
            'formatUpdate:slugs:slugSourceValue:modelValue:fallback',
            $input['event']
        );
        $this->assertFalse($arrayable);
    }

    /** @test */
    public function it_falls_back_to_ext_event_dsl_when_form_events_is_absent(): void
    {
        $input = [
            'type' => 'text',
            'name' => 'status',
            'ext' => 'lock:url:url|clearModel:status',
        ];
        $data = null;
        $arrayable = false;

        FormEventCompiler::apply($input, $data, $arrayable, []);

        $this->assertStringContainsString('formatLock:url:url', $input['event']);
        $this->assertStringContainsString('formatClearModel:status', $input['event']);
        $this->assertContains('formatLock:url:url', $input['formEvents']);
    }

    /** @test */
    public function form_events_wins_when_both_keys_are_present(): void
    {
        $input = [
            'type' => 'text',
            'name' => 'title',
            'formEvents' => 'update:name:modelValue:modelValue',
            'ext' => 'lock:url:url',
        ];
        $data = null;
        $arrayable = false;

        $source = FormEventCompiler::resolveSource($input);
        $this->assertFalse($source['fromExt']);
        $this->assertSame(['update:name:modelValue:modelValue'], $source['patterns']);

        FormEventCompiler::apply($input, $data, $arrayable, []);

        $this->assertSame('formatUpdate:name:modelValue:modelValue', $input['event']);
        $this->assertStringNotContainsString('formatLock', $input['event']);
    }

    /** @test */
    public function type_alias_ext_does_not_compile_events(): void
    {
        $input = [
            'type' => 'text',
            'name' => 'published_at',
            'ext' => 'date',
        ];
        $data = null;
        $arrayable = false;

        FormEventCompiler::apply($input, $data, $arrayable, []);

        $this->assertSame(date('Y-m-d'), $input['default']);
        $this->assertArrayNotHasKey('event', $input);
        $this->assertArrayNotHasKey('formEvents', $input);
    }

    /** @test */
    public function nested_array_and_pipe_string_compile_the_same_tokens(): void
    {
        $fromString = [
            'type' => 'radio-group',
            'name' => 'type',
            'formEvents' => 'toggleInput:transfer_details:items.*.transfer_details_toggleInputValue',
        ];
        $fromArray = [
            'type' => 'radio-group',
            'name' => 'type',
            'formEvents' => [
                ['toggleInput', 'transfer_details', 'items.*.transfer_details_toggleInputValue'],
            ],
        ];
        $dataA = $dataB = null;
        $arrayableA = $arrayableB = false;

        FormEventCompiler::apply($fromString, $dataA, $arrayableA, []);
        FormEventCompiler::apply($fromArray, $dataB, $arrayableB, []);

        $this->assertSame($fromString['event'], $fromArray['event']);
        $this->assertSame(
            'formatToggleInput:transfer_details:items.*.transfer_details_toggleInputValue:-1',
            $fromString['event']
        );
    }

    /** @test */
    public function it_merges_existing_event_pipe_and_keeps_set_tail_args(): void
    {
        $input = [
            'type' => 'text',
            'name' => 'title',
            'formEvents' => [
                ['set', 'wrap1.schema.name', 'modelValue', 'modelValue', 'fallback'],
            ],
            'event' => 'existing',
        ];
        $data = null;
        $arrayable = false;

        FormEventCompiler::apply($input, $data, $arrayable, []);

        $this->assertStringContainsString('existing', $input['event']);
        $this->assertStringContainsString(
            'formatSet:wrap1.schema.name:modelValue:modelValue:fallback',
            $input['event']
        );
    }

    /** @test */
    public function resolve_source_treats_empty_form_events_as_absent(): void
    {
        $resolved = FormEventCompiler::resolveSource([
            'name' => 'title',
            'formEvents' => [],
            'ext' => 'update:slugs:slugSourceValue:modelValue',
        ]);

        $this->assertTrue($resolved['fromExt']);
        $this->assertSame(['update:slugs:slugSourceValue:modelValue'], $resolved['patterns']);
    }
}
