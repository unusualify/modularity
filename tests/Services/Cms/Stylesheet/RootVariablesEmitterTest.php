<?php

namespace Unusualify\Modularous\Tests\Services\Cms\Stylesheet;

use Modules\Cms\Services\Stylesheet\RootVariablesEmitter;
use Unusualify\Modularous\Tests\TestCase;

final class RootVariablesEmitterTest extends TestCase
{
    public function test_emits_root_block_when_properties_valid(): void
    {
        $emitter = new RootVariablesEmitter;

        $css = $emitter->emit([
            '--color-primary' => '#0f766e',
            'spacing-unit' => '8px',
        ]);

        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--color-primary: #0f766e;', $css);
        $this->assertStringContainsString('--spacing-unit: 8px;', $css);
    }

    public function test_skips_unknown_property_names_and_empty_values(): void
    {
        $emitter = new RootVariablesEmitter;

        $css = $emitter->emit([
            '--ok' => '1rem',
            'bad name' => '#fff',
            '--empty' => '',
        ]);

        $this->assertStringContainsString('--ok: 1rem;', $css);
        $this->assertStringNotContainsString('--empty', $css);
    }
}
