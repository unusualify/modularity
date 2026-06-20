<?php

namespace Unusualify\Modularous\Tests\Hydrates\Inputs;

use Unusualify\Modularous\Hydrates\Inputs\EditorHydrate;
use Unusualify\Modularous\Tests\TestCase;

class EditorHydrateTest extends TestCase
{
    /** @test */
    public function it_hydrates_editor_input_schema(): void
    {
        $hydrate = new EditorHydrate([
            'type' => 'editor',
            'name' => 'content',
            'label' => 'Body',
            'height' => 420,
            'translated' => true,
        ]);

        $input = $hydrate->render();

        $this->assertSame('input-editor', $input['type']);
        $this->assertSame('content', $input['name']);
        $this->assertSame('Body', $input['label']);
        $this->assertSame(420, $input['height']);
        $this->assertTrue($input['translated']);
        $this->assertContains('default', $input['translatedProps']);
    }

    /** @test */
    public function it_decodes_json_toolbar_and_editor_config(): void
    {
        $hydrate = new EditorHydrate([
            'type' => 'editor',
            'name' => 'content',
            'toolbar' => '{"items":["bold","italic"]}',
            'editorConfig' => '{"placeholder":"Write here..."}',
        ]);

        $input = $hydrate->render();

        $this->assertSame(['items' => ['bold', 'italic']], $input['toolbar']);
        $this->assertSame(['placeholder' => 'Write here...'], $input['editorConfig']);
    }
}
