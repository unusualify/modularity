<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

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

    public function afterHydrateRecords(&$input) {}
}
