<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;

class StateableHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'label' => 'Status',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['name'] = '_state';
        $input['type'] = 'select';
        $input['itemTitle'] = 'name';
        $input['itemValue'] = 'id';

        $repository = App::make('Modules\SystemUtility\Repositories\StateRepository');

        $model = App::make('Modules\PressRelease\Entities\PressRelease');
        // dd();
        $states = $repository->getByColumnValues('code', array_column($model->default_states, 'code'));
        // dd($states);
        $items = [];
        foreach ($states as $state) {
            array_push(
                $items,
                [
                    'id' => $state->id,
                    'name' => $state->translatedAttribute('name')->first(),
                ]
            );
        }
        $input['items'] = $items;

        return $input;
    }

    public function createNonExistantStates()
    {
        //
    }
}
