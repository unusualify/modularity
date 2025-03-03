<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

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

        // $repository = App::make('Modules\SystemUtility\Repositories\StateRepository');

        $module = null;
        $routeName = null;
        if(isset($input['_moduleName'])) {
            $module = Modularity::find($input['_moduleName']);
        } else if($this->hasModule()) {
            $module = $this->module;
        } else {
            throw new \Exception("No Module in '{$input['name']} input'");
        }

        if(isset($input['_routeName'])) {
            $routeName = Modularity::find($input['_moduleName']);
        } else if($this->hasRouteName()) {
            $routeName = $this->getRouteName();
        } else {
            throw new \Exception("No Route in '{$input['name']} input'");
        }

        // If default_states contains strings, convert them to objects first
        $model = App::make($module->getRouteClass($routeName, 'model'));

        $input['items'] = $model->getStateableList();

        return $input;
    }
}
