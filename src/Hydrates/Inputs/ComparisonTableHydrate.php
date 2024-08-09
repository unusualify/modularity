<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

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
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;
        $input['type'] = 'input-comparison-table';


        return $input;
    }

    /**
     *  Withs defined on the input to add to model's withs
     *
     * @return array
     */
    public function withs() :array
    {
        return array_keys($this->input['comparators'] ?? []);
    }

}
