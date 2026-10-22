<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Facades\Modularous;

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
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['name'] = 'stateable_id';
        $input['type'] = 'select';
        $input['itemTitle'] = 'name';
        $input['itemValue'] = 'id';

        // $repository = App::make('Modules\SystemUtility\Repositories\StateRepository');

        $module = null;
        $routeName = null;
        if (isset($input['_moduleName'])) {
            $module = Modularous::find($input['_moduleName']);
        } elseif ($this->hasModule()) {
            $module = $this->module;
        } else {
            throw new \Exception("No Module in '{$input['name']} input'");
        }

        if (isset($input['_routeName'])) {
            $routeName = $input['_routeName'];
        } elseif ($this->hasRouteName()) {
            $routeName = $this->getRouteName();
        } else {
            throw new \Exception("No Route in '{$input['name']} input'");
        }

        // If default_states contains strings, convert them to objects first
        $repository = App::make($module->getRouteClass($routeName, 'repository'));

        $input['items'] = ! $this->skipQueries
            ? $repository->getStateableList(itemValue: 'name')
            : [];

        return $input;
    }
}
