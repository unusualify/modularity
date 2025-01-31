<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class SwitchHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'color' => 'success',
        'trueValue' => 1,
        'falseValue' => 0,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['hideDetails'] = $input['hideDetails'] ?? true;
        $input['default'] = $input['default'] ?? 1;

        // add your logic

        return $input;
    }
}
