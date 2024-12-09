<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class TagHydrate extends InputHydrate
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
        'cascadeKey' => 'items',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-tag';

        if (isset($input['_moduleName'])) {
            $module = Modularity::find($input['_moduleName']);

            $repository = $module->getRouteClass($input['_routeName'], 'repository');
            $repository = App::make($repository);

            $input['returnObject'] = false;
            $input['chips'] = false;
            $input['multiple'] ??= false;

            $input['endpoint'] = $module->getRouteActionUri($input['_routeName'], 'tags');
            $input['updateEndpoint'] = $module->getRouteActionUri($input['_routeName'], 'tagsUpdate');
            $input['items'] = array_merge($repository->getTags()->toArray());
            $input['taggable'] = get_class($repository->getModel());

            if (! isset($input['default']) && count($input['items']) > 0) {
                $input['default'] = $input['items'][0][$input['itemValue']];
            }
            // dd($input, $repository->getTags()->toArray());

            // dd(
            //     $repository->getTagsList(),
            //     $repository->getTags()
            // );
        }

        return $input;
    }
}
