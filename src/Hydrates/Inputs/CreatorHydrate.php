<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

class CreatorHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'label' => 'Creator',
        'itemTitle' => 'email_with_company',
        'appends' => ['email_with_company'],
        'with' => ['company'],
        'allowedRoles' => ['superadmin'],
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate()
    {
        $input = $this->input;

        // add your logic
        $input['type'] = 'input-browser';

        $input['name'] = 'custom_creator_id';
        $input['multiple'] = false;
        $input['itemValue'] = 'id';
        $input['returnObject'] = false;

        $input['col'] = [
            'cols' => 12,
        ];

        $input['endpoint'] = route('admin.system.user.index', [
            'light' => true,
            'eager' => $input['with'],
            'appends' => $input['appends'],
        ]);
        unset($input['appends'], $input['with']);

        // add your logic

        return $input;
    }
}
