<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class RelationshipsHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'color' => 'grey',
        'cardVariant' => 'outlined',
        'processableTitle' => 'name',
        'eager' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        return $input;
    }
}
