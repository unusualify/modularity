<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class FormTabsHydrate extends InputHydrate
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
        $input['type'] = 'input-form-tabs';
        $input['default'] = json_decode('{}');

        $eagers = [];
        $lazy = [];
        foreach ($input['schema'] as $key => $_input) {
            if (isset($_input['noEager']) && $_input['noEager'] === true) {
                continue;
            }
            if ($_input['type'] == 'input-comparison-table' && isset($_input['comparators'])) {
                foreach ($_input['comparators'] as $relation => $conf) {
                    if (isset($conf['lazy'])) {
                        $lazy[] = is_array($conf['lazy']) ? $conf['lazy'] : explode(',', $conf['lazy']);
                    } else {
                        $eagers[] = isset($conf['eager'])
                            ? (is_array($conf['eager']) ? $conf['eager'] : explode(',', $conf['eager']))
                            : [$input['name'] . '.' . $relation];
                    }
                }
            }
            if (in_array($_input['type'], ['checklist', 'input-checklist', 'select', 'combobox', 'autocomplete'])) {
                if (isset($_input['lazy'])) {
                    $lazy[] = is_array($_input['lazy']) ? $_input['lazy'] : explode(',', $_input['lazy']);
                } else {
                    $eagers[] = isset($_input['eager'])
                        ? (is_array($_input['eager']) ? $_input['eager'] : explode(',', $_input['eager']))
                        : $input['name'] . '.' . $_input['name'];
                }
            }
        }

        $input['eagers'] = array_reduce($eagers, function ($acc, $item) {
            $acc = array_merge($acc, $item);

            return $acc;
        }, []);

        $input['lazy'] = array_reduce($lazy, function ($acc, $item) {
            $acc = array_merge($acc, $item);

            return $acc;
        }, []);

        return $input;
    }
}
