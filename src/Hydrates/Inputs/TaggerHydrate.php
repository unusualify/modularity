<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularous\Facades\Modularous;

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
     */
    public function hydrate()
    {
        $input = $this->input;

        $translated = $input['translated'] ?? false;

        // add your logic
        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $module = Modularous::find($input['_moduleName']);
            $repository = $module->getRouteClass($input['_routeName'], 'repository');
            $repository = App::make($repository);

            if (! classHasTrait($repository, 'Unusualify\Modularous\Repositories\Traits\TagsTrait')) {
                throw new \Exception('Repository ' . $repository . ' does not have TagsTrait in ' . $this->input['name'] . ' input');
            }

            $input['fetchEndpoint'] = $module->getRouteActionUrl($input['_routeName'], 'tags');
            $input['updateEndpoint'] = $module->getRouteActionUrl($input['_routeName'], 'tagsUpdate');

            $items = ! $this->skipQueries ? $repository->getTags(translated: $translated, map: fn ($tag, $index) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $input['colors'][$index % count($input['colors'])],
            ])->toArray() : [];

            if ($translated) {
                $input['items'] = collect($items)->map(function ($group) {
                    array_unshift($group, ['header' => true, 'name' => __('Select an option or create one')]);

                    return $group;
                })->toArray();
            } else {
                $input['items'] = array_merge([['header' => true, $input['itemTitle'] => __('Select an option or create one')]], $items);
            }

            $input['taggable'] = get_class($repository->getModel());

        } else {
            throw new \Exception('Invalid input for ' . $this->input['name'] . ' input');
        }

        $input['type'] = 'input-tagger';

        return $input;
    }
}
