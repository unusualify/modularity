<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class BrowserHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'default' => null,
        'returnObject' => false,
        'label' => 'Browser',
        'multiple' => false,
        'max' => null,
        'objectModelValues' => ['*'],
        'objectIdDefiner' => null,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-browser';

        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $module = Modularity::find($input['_moduleName']);
            $repository = $module->getRouteClass($input['_routeName'], 'repository');
            $repository = App::make($repository);

            $input['endpoint'] = $module->getRouteActionUrl($input['_routeName'], 'index');
        }

        // add your logic

        return $input;
    }
}
