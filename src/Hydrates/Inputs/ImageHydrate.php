<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

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
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-image';
        $input['label'] ??= __('Images');

        return $input;
    }
}
