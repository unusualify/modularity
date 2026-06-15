<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\JsonFieldHydrate;
use Unusualify\Modularous\Tests\TestCase;

final class JsonFieldHydrateTest extends TestCase
{
    public function test_json_field_sets_input_type_and_merges_snippets(): void
    {
        $input = [
            'type' => 'json-field',
            'name' => 'definition',
            'jsonSnippets' => [
                ['label' => 'Custom', 'insert' => ['a' => 1]],
            ],
        ];

        $h = new JsonFieldHydrate($input, null, null, true);

        $out = $h->render();

        $this->assertSame('input-json-field', $out['type']);
        $this->assertIsArray($out['jsonSnippets']);
        $this->assertGreaterThanOrEqual(3, count($out['jsonSnippets']));
        $this->assertSame('{}', $out['jsonSnippets'][0]['label']);
        $this->assertSame('Custom', $out['jsonSnippets'][2]['label']);
    }
}
