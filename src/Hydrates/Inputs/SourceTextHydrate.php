<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Hydrates\Inputs;

/**
 * Hydrates {@code source-text} into {@code input-source-text} for {@code VInputSourceText} (SourceText.vue).
 *
 * Schema-only {@code format} (user cannot change it in the UI): {@code md}|{@code txt}|{@code js}|{@code php}|{@code html}.
 * Vue maps {@code format} to a CodeMirror 6 language in the modal editor.
 * {@code md} and {@code html} also get an Edit / Preview toggle (markdown via marked; HTML in a sandboxed iframe).
 * Optional passthrough: {@code rows} (min editor height), {@code autoGrow}, {@code variant}, {@code density}, {@code persistentHint}.
 */
class SourceTextHydrate extends InputHydrate
{
    /**
     * @var list<string>
     */
    public const FORMATS = ['md', 'txt', 'js', 'php', 'html'];

    /**
     * @var array<string, mixed>
     */
    public $requirements = [
        'format' => 'md',
        'rows' => 18,
        'autoGrow' => true,
        'variant' => 'outlined',
        'density' => 'comfortable',
        'persistentHint' => true,
    ];

    public function hydrate(): array
    {
        $input = $this->input;

        $defaultCol = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($defaultCol, $input['col'] ?? []);
        $input['type'] = 'input-source-text';

        $format = mb_strtolower((string) ($input['format'] ?? 'md'));
        $input['format'] = in_array($format, self::FORMATS, true) ? $format : 'md';

        return $input;
    }
}
