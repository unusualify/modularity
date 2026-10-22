<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class ComparisonTableHydrate extends InputHydrate
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
        $input['type'] = 'input-comparison-table';

        return $input;
    }

    /**
     *  Withs defined on the input to add to model's withs
     */
    public function withs(): array
    {
        return array_keys($this->input['comparators'] ?? []);
    }
}
