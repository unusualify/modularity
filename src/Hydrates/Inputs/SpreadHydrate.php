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
        // dd($input);
        // $input['items'] = ['test'];
        if (in_array('scrollable', $input)) {
            $input = array_diff($input, ['scrollable']);
            $input['scrollable'] = true;

        }

        $module = Modularity::find($input['_moduleName']);
        // dd($module);
        // dd($module->getRepository($input['_routeName']));
        $repository = $module->getRepository($input['_routeName']);
        $model = App::make($module->getRouteClass($input['_routeName'], 'model'));
        // dd($repository, get_class_methods($repository));
        // dd($repository);
        if(!isset($input['reservedKeys'])){
            $input['reservedKeys'] = $model->getReservedKeys();
        }

        // $allInputs = $model->getRouteInputs();
        // dd($model->getRouteInputs());
        // $spreadableInputs = collect($model->getRouteInputs())
        //     ->filter(function ($item) {
        //         return isset($item['spreadable']) && $item['spreadable'] === true;
        //     })
        //     ->pluck('name');
        $spreadableInputs = $repository->getSpreadableInputKeys($model);
        // dd($spreadableInputs);
        if(!empty($spreadableInputs) || $spreadableInputs){
            $input['reservedKeys'] = array_merge($input['reservedKeys'], $spreadableInputs);
        }
        // dd($input['reservedKeys']);
        // $input['reservedKeys'] = collect($this->module->getRouteInput($input['_routeName']))
        // ->filter(fn($item) => $item['name'] !== '_spread')
        // ->pluck('name')
        // ->toArray();

        // dd($reservedKeys);
        // dd($input, $this->module, get_class_methods($this->module));
        return $input;
    }
}
