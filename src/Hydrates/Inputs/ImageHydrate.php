<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class ImageHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'images',
        'translated' => false,
        'default' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-image';
        $input['label'] ??= __('Images');

        return $input;
    }
}
