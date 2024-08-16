<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class JsonHydrate extends InputHydrate
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

        $default_col = [
            'cols' => 12,
        ];
        $input['col'] = array_merge_recursive_preserve($default_col, $input['col'] ?? []);
        $input['type'] = 'group';

        return $input;
    }
}
