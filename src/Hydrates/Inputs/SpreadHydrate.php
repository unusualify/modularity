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
        $model = App::make($module->getRouteClass($input['_routeName'], 'model'));
        // dd($model);

        if (! isset($input['reservedKeys'])) {
            $input['reservedKeys'] = $model->getReservedKeys();
        }

        // $allInputs = $model->getRouteInputs();
        $spreadableInputs = collect($model->getRouteInputs())
            ->filter(function ($item) {
                return isset($item['spreadable']) && $item['spreadable'] === true;
            })
            ->pluck('name');

        if (! empty($spreadableInputs) || $spreadableInputs) {
            // dd( array_merge($input['reservedKeys'], $spreadableInputs->toArray()));
            $input['reservedKeys'] = array_merge($input['reservedKeys'], $spreadableInputs->toArray());
        }

        $input['col'] = [
            'cols' => 12,
            'sm' => 12,
            'md' => 12,
            'lg' => 12,
            'xl' => 12,
        ];

        $input['name'] = $model->getSpreadableSavingKey();
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
