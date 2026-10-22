<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class AutocompleteHydrate extends InputHydrate
{
    public $selectable = true;

    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => [],
        'cascadeKey' => 'items',
        'returnObject' => false,
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        if ((! isset($input['multiple']) || ! in_array('multiple', $input)) && is_array($input['default'])) {
            $input['default'] = null;
        }

        if (isset($input['items']) && ! empty($input['items'])) {
            return $input;
        }

        if (($input['type'] == 'select-scroll' || (isset($input['ext']) && $input['ext'] == 'scroll'))
            && (isset($input['endpoint']) || isset($input['connector']))
        ) {
            $input['componentType'] = 'v-autocomplete';
            $input['type'] = 'input-select-scroll';

            unset($input['ext']);
        }

        return $input;
    }
}
