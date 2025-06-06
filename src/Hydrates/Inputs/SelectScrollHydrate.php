<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Arr;

class SelectScrollHydrate extends InputHydrate
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
        'returnObject' => false,
        'itemsPerPage' => 10,
        'multiple' => false,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        if (isset($input['items']) && ! empty($input['items'])) {
            return $input;
        }

        if (! isset($input['endpoint'])) {
            throw new \Exception('Endpoint is required ' . $input['name']);
        }

        $input['componentType'] ??= 'v-autocomplete';
        $input['type'] = 'input-select-scroll';
        // unset($input['ext']);

        $input['default'] = $input['multiple'] ? [] : null;
        $input['items'] = [];
        $input['noRecords'] = true;

        return $input;
    }

    /**
     *  Handle input after records set
     *
     * @return void
     */
    public function afterHydrateRecords(&$input)
    {
        if (isset($input['cascades'])) {
            $items = $input['items'];

            $input['cascadeKey'] ??= 'items';

            $patterns = [];
            foreach ($input['cascades'] as $key => $cascade) {
                $explodes = explode('.', explode(':', $cascade)[0]);
                $patterns[] = "/{$this->getSnakeCase(
                    $explodes[count($explodes) - 1]
                )}/";
            }
            $flat = Arr::dot($items);
            $newArray = [];
            foreach ($flat as $key => $value) {
                $newKey = preg_replace($patterns, 'items', $key);
                Arr::set($newArray, $newKey, $value);
            }

            $input['items'] = $newArray;
        }

        if (
            isset($input['items'])
            && count($input['items'])
            && isset($input['items'][0][$input['itemValue']])
            && $input['items'][0][$input['itemValue']]
        ) {
            array_unshift($input['items'], [
                $input['itemValue'] => 0,
                $input['itemTitle'] => 'Please Select',
            ]);
        }
    }
}
