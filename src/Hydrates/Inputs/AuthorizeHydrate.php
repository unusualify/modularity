<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class AuthorizeHydrate extends InputHydrate
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
        'label' => 'Authorize',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'select';
        $input['name'] = 'authorized_id';
        $input['multiple'] = false;
        $input['returnObject'] = false;

        $input['rules'] = 'sometimes|required';

        // add your logic

        return $input;
    }

    public function afterHydrateRecords(&$input)
    {
        if(!isset($input['items'])){
            dd($input);
            return;
        }
        // dd($input);
    }

}
