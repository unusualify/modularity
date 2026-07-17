<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemSetting;

use Modules\SystemSetting\Support\SystemSettingsInputMerger;
use Unusualify\Modularous\Tests\TestCase;

class SystemSettingsInputMergerTest extends TestCase
{
    public function test_merges_extra_inputs_and_overrides(): void
    {
        config([
            'modularous.system_settings.extra_inputs' => [
                ['name' => 'custom_flag', 'type' => 'switch', 'label' => 'Custom'],
            ],
            'modularous.system_settings.input_overrides' => [
                'site' => ['label' => 'Site (custom)'],
            ],
            'modularous.system_settings.hidden_inputs' => ['analytics'],
        ]);

        $merger = new SystemSettingsInputMerger;

        $merged = $merger->merge([
            ['name' => 'site', 'type' => 'group', 'label' => 'Site', 'schema' => []],
            ['name' => 'analytics', 'type' => 'group', 'label' => 'Analytics', 'schema' => []],
        ]);

        $this->assertSame('Site (custom)', $merged[0]['label']);
        $this->assertSame('custom_flag', $merged[1]['name']);
        $this->assertCount(2, $merged);
    }

    public function test_expands_input_aliases(): void
    {
        config([
            'modularous.input_types.@test_alias' => [
                'type' => 'text',
                'name' => 'alias_field',
                'label' => 'Alias field',
            ],
        ]);

        $merger = new SystemSettingsInputMerger;

        $merged = $merger->merge([['type' => '@test_alias']]);

        $this->assertSame('alias_field', $merged[0]['name']);
    }
}
