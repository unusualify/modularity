<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

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
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        dd(
            $this->getModule(),
            $this->getRouteName(),
        );

        return $input;
    }
}
