<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class ChecklistGroupHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'default' => []
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-checklist-group';

        if(isset($input['schema'])){

            $input['schema'] = array_filter($input['schema'], function($_input){
                return isset($_input['items']) && !empty($_input['items']);
            });
        }
        return $input;
    }
}
