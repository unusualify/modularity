<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class TabGroupHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'default' => [],
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;
        $input['type'] = 'input-tab-group';
        $input['default'] = json_decode('{}');

        $eagers = [];
        foreach ($input['schema'] as $key => $_input) {
            if ($_input['type'] == 'input-comparison-table' && isset($_input['comparators'])) {
                foreach ($_input['comparators'] as $relation => $conf) {
                    $eagers[] = isset($conf['eager']) ? $conf['eager'] : $input['name'] . '.' . $relation;
                }
            }
            if (in_array($_input['type'], ['checklist', 'input-checklist', 'select', 'combobox', 'autocomplete'])) {
                $eagers[] = isset($_input['eager']) ? $_input['eager'] : $input['name'] . '.' . $_input['name'];
            }
        }
        $input['eagers'] = $eagers;

        return $input;
    }
}
