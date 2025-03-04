<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;

class TaggerHydrate extends InputHydrate
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
        'default' => [],
        'returnObject' => false,
        'label' => 'Tags',
        'name' => 'tags',
        'colors' => ['green', 'purple', 'indigo', 'cyan', 'teal', 'orange'],
        'multiple' => true,
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
        $modelInstance = null;
        if (isset($input['modelx'])) {
            if (! class_exists($input['model'])) {
                throw new \Exception('Model ' . $input['model'] . ' does not exist in ' . $this->input['name'] . ' input');
            }

            $modelInstance = App::make($input['model']);
        } elseif (isset($input['repositoryx'])) {
            if (! class_exists($input['repository'])) {
                throw new \Exception('Repository ' . $input['repository'] . ' does not exist in ' . $this->input['name'] . ' input');
            }

            $repositoryInstance = App::make($input['repository']);

            $modelInstance = $repositoryInstance->getModel();
        } elseif (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $module = Modularity::find($input['_moduleName']);
            $repository = $module->getRouteClass($input['_routeName'], 'repository');
            $repository = App::make($repository);

            if (! classHasTrait($repository, 'Unusualify\Modularity\Repositories\Traits\TagsTrait')) {
                throw new \Exception('Repository ' . $repository . ' does not have TagsTrait in ' . $this->input['name'] . ' input');
            }

            $input['fetchEndpoint'] = $module->getRouteActionUri($input['_routeName'], 'tags');
            $input['updateEndpoint'] = $module->getRouteActionUri($input['_routeName'], 'tagsUpdate');
            // $repository->getTagsList()->toArray() // this get used tags
            // $repository->getTags()->toArray() // this get all tags

            $items = !$this->skipQueries
                ? $repository->getTags()->map(fn ($tag, $index) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $input['colors'][$index % count($input['colors'])],
                ])->toArray()
                : [];

            $input['items'] = array_merge([['header' => true, $input['itemTitle'] => __('Select an option or create one')]], $items);
            $input['taggable'] = get_class($repository->getModel());

        } else {
            throw new \Exception('Invalid input for ' . $this->input['name'] . ' input');
        }

        $input['type'] = 'input-tagger';

        return $input;
    }
}
