<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates\Inputs;

/**
 * Hydrates {@code json-field} into {@code input-json-field} for the {@code VInputJsonField} Vue component (JsonField.vue).
 *
 * Extra schema props (optional, passed through to Vue):
 * - {@code rows}, {@code maxRows}, {@code autoGrow}
 * - {@code jsonIndent} (default 2) — pretty-print indent depth
 * - {@code jsonSnippets}: list of presets {@code [['label'=>string,'insert'=>mixed], ...]}
 * - UI yönergeler (isteğe bağlı): {@code jsonFieldSubtitle}, {@code jsonFieldVariantChips} ({@code [[label,detail]], ...}), {@code jsonFieldGuideTitle} + {@code jsonFieldGuideSections} ({@code [[title,body]], ...})
 * - {@code validateOn}: forwarded to textarea (blur recommended for noisy JSON edits)
 *
 * Laravel {@code rules} strings still apply server-side; the Vue field validates editable JSON syntax client-side.
 */
class JsonFieldHydrate extends InputHydrate
{
    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'rows' => 14,
        'autoGrow' => true,
        'maxRows' => 36,
        'jsonIndent' => 2,
        'variant' => 'outlined',
        'density' => 'comfortable',
        'persistentHint' => true,
        /** @var string Matches Vuetify 3 textarea prop */
        'validateOn' => 'blur lazy',
    ];

    public function hydrate(): array
    {
        $input = $this->input;

        $defaultCol = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($defaultCol, $input['col'] ?? []);
        $input['type'] = 'input-json-field';

        /** @var list<array<string, mixed>> */
        $defaultSnippets = [
            ['label' => '{}', 'insert' => (object) []],
            ['label' => '[]', 'insert' => []],
        ];

        $merged = array_merge($defaultSnippets, isset($input['jsonSnippets']) && is_array($input['jsonSnippets']) ? $input['jsonSnippets'] : []);
        $input['jsonSnippets'] = array_values(array_filter($merged, static fn ($row) => is_array($row) && isset($row['label'])));

        return $input;
    }
}
