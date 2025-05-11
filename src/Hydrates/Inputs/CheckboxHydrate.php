<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class CheckboxHydrate extends InputHydrate
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
        'hideDetails' => true,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        // $input['hideDetails'] ??= true;
        $input['default'] = 0;

        return $input;
    }
}
