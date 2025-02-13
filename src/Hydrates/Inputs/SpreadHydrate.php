<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class SpreadHydrate extends InputHydrate
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
        // add your logic
        $input['type'] = 'input-spread';

        if (in_array('scrollable', $input)) {
            $input = array_diff($input, ['scrollable']);
            $input['scrollable'] = true;

        }

        $module = Modularity::find($input['_moduleName']);

        $repository = $module->getRepository($input['_routeName']);
        $model = App::make($module->getRouteClass($input['_routeName'], 'model'));

        if(!isset($input['reservedKeys'])){
            $input['reservedKeys'] = $model->getReservedKeys();
        }


        $spreadableInputs = $repository->getSpreadableInputKeys($model);

        if(!empty($spreadableInputs) || $spreadableInputs){
            $input['reservedKeys'] = array_merge($input['reservedKeys'], $spreadableInputs);
        }

        return $input;
    }
}
