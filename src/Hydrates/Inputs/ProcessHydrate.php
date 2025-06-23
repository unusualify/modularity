<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class ProcessHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'color' => 'grey',
        'cardVariant' => 'outlined',
        'processableTitle' => 'name',
        'eager' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $module = Modularity::find($input['_moduleName']);
            $model = $module->getRouteClass($input['_routeName'], 'model');
            $mode = App::make($model);

            if (! classHasTrait($mode, 'Unusualify\Modularity\Entities\Traits\Processable')) {
                throw new \Exception('Model ' . $model . ' does not have ProcessableTrait in ' . $this->input['name'] . ' input');
            }

            $eager = implode(',', $input['eager']);
            $input['fetchEndpoint'] = route('admin.process.show', [
                'process' => ':id',
                ...($eager ? ['eager' => $eager] : []),
            ]);
            $input['updateEndpoint'] = route('admin.process.update', ['process' => ':id']);

        } else {
            throw new \Exception('Invalid input for ' . $this->input['name'] . ' input');
        }

        // dd($input);
        $input['name'] = 'process_id';
        $input['type'] = 'input-process';

        return $input;
    }
}
