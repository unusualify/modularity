<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Hydrates;

use Unusualify\Modularous\Hydrates\Inputs\SourceTextHydrate;
use Unusualify\Modularous\Tests\TestCase;

final class SourceTextHydrateTest extends TestCase
{
    public function test_source_text_sets_input_type_format_and_full_width(): void
    {
        $h = new SourceTextHydrate([
            'type' => 'source-text',
            'name' => 'llms_txt',
            'format' => 'md',
        ], null, null, true);

        $out = $h->render();

        $this->assertSame('input-source-text', $out['type']);
        $this->assertSame('md', $out['format']);
        $this->assertSame(12, $out['col']['cols']);
        $this->assertSame(18, $out['rows']);
        $this->assertTrue($out['autoGrow']);
    }

    public function test_invalid_format_falls_back_to_md(): void
    {
        $h = new SourceTextHydrate([
            'type' => 'source-text',
            'name' => 'body',
            'format' => 'json',
        ], null, null, true);

        $out = $h->render();

        $this->assertSame('md', $out['format']);
    }

    public function test_allowed_formats_are_normalized_to_lowercase(): void
    {
        $h = new SourceTextHydrate([
            'type' => 'source-text',
            'name' => 'head',
            'format' => 'PHP',
        ], null, null, true);

        $out = $h->render();

        $this->assertSame('php', $out['format']);
    }
}
