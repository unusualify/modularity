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

        $input['name'] = '_stateable';
        $input['type'] = 'select';
        $input['itemTitle'] = 'name';
        $input['itemValue'] = 'id';

        $repository = App::make('Modules\SystemUtility\Repositories\StateRepository');

        $model = App::make('Modules\PressRelease\Entities\PressRelease');

        // If default_states contains strings, convert them to objects first
        $stateObjects = is_string(reset($model->default_states))
            ? $this->stringArrayToObjectArray($model->default_states, 'code')
            : $model->default_states;

        $states = $repository->getByColumnValues('code', array_column($stateObjects, 'code'));
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

    /**
     * Convert an array of strings to an array of objects with a specified key
     *
     * @param array $array Array of strings to convert
     * @param string $targetKey The object key that will hold the string value
     * @return array Array of objects
     */
    protected function stringArrayToObjectArray($array, $targetKey)
    {
        $objectArray = [];
        foreach($array as $item) {
            if(is_string($item)) {
                $obj = new \stdClass();
                $obj->$targetKey = $item;
                $objectArray[] = $obj;
            }
        }
        return $objectArray;
    }
}
