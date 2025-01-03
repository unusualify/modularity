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

        $repository = App::make('Modules\SystemUtility\Repositories\StateRepository');

        // If default_states contains strings, convert them to objects first
        $module = Modularity::find($input['_moduleName']);
        $model = App::make($module->getRouteClass($input['_routeName'], 'model'));

        $input['items'] = $model->getStateableList();

        return $input;
    }
}
