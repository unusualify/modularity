<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class RadioGroupHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-radio-group';

        if (count($input['items']) > 0) {
            $input['default'] = $input['items'][0][$input['itemValue']];
        }

        return $input;
    }
}
