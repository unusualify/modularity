<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Facades\Modularous;

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

        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $module = Modularous::find($input['_moduleName']);
            $model = App::make($module->getRouteClass($input['_routeName'], 'model'));

            if (! isset($input['reservedKeys'])) {
                $input['reservedKeys'] = $model->getReservedKeys();
            }

            $spreadableInputs = collect($model->getRouteInputs())
                ->filter(function ($item) {
                    return isset($item['spreadable']) && $item['spreadable'] === true;
                })
                ->pluck('name');

            if (! empty($spreadableInputs) || $spreadableInputs) {
                $input['reservedKeys'] = array_merge($input['reservedKeys'], $spreadableInputs->toArray());
            }

            $input['name'] = $model->getSpreadableSavingKey();
        } else {
            if (! isset($input['reservedKeys'])) {
                $input['reservedKeys'] = [];
            }
            if (! isset($input['name'])) {
                $input['name'] = 'spread_payload';
            }
        }

        $input['col'] = [
            'cols' => 12,
            'sm' => 12,
            'md' => 12,
            'lg' => 12,
            'xl' => 12,
        ];

        return $input;
    }
}
