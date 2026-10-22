<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class DateHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [

    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-date';

        return $input;
    }
}
