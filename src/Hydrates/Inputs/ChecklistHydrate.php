<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

class ChecklistHydrate extends InputHydrate
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

        $input['type'] = 'input-checklist';

        return $input;
    }

    public function afterHydrateRecords(&$input)
    {

    }
}
