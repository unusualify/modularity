<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class FileHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'files',
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

        $input['type'] = 'input-file';
        $input['label'] ??= __('Files');

        return $input;
    }
}
