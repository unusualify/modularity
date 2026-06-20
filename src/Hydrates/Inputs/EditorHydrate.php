<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\Route;

class EditorHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     * @var array
     */
    public $requirements = [
        'name' => 'content',
        'label' => 'Content',
        'default' => '',
        'height' => 300,
        'translated' => false,
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate(): array
    {
        $input = $this->input;

        $input['type'] = 'input-editor';
        $input['label'] ??= __('Content');
        $input['default'] ??= '';

        if ($input['translated'] ?? false) {
            $this->addTranslatedProps($input, 'default');
        }

        if (isset($input['uploadEndpoint'])) {
            $input['uploadUrl'] = resolve_route($input['uploadEndpoint']);
            unset($input['uploadEndpoint']);
        } elseif (! isset($input['uploadUrl']) && Route::hasAdmin('media-library.media.store')) {
            $input['uploadUrl'] = resolve_route('media-library.media.store');
        }

        if (isset($input['toolbar']) && is_string($input['toolbar'])) {
            $decoded = json_decode($input['toolbar'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $input['toolbar'] = $decoded;
            }
        }

        if (isset($input['editorConfig']) && is_string($input['editorConfig'])) {
            $decoded = json_decode($input['editorConfig'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $input['editorConfig'] = $decoded;
            }
        }

        return $input;
    }
}
