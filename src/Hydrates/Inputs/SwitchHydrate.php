<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

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

        $input['hideDetails'] = true;
        $input['default'] = 0;

        // add your logic

        return $input;
    }
}
