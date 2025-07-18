<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Unusualify\Modularity\Hydrates\InputHydrator;

if (! function_exists('configure_input')) {
    function configure_input(array $input)
    {
        return collect($input)
            ->mapWithKeys(function ($v, $k) {
                if ($k == 'label'
                    && ($translation = ___("form-labels.{$v}")) !== "form-labels.{$v}"
                ) {
                    $v = $translation;
                }

                return is_numeric($k) ? [$v => true] : [$k => $v];
            })
            ->toArray();
    }
}

if (! function_exists('modularity_default_input')) {
    function modularity_default_input()
    {
        return (array) Config::get(modularityBaseKey() . '.default_input');
    }
}

if (! function_exists('hydrate_input')) {
    function hydrate_input(array $input, $module = null, $routeName = null, $skipQueries = null)
    {
        $input = hydrate_input_type($input);

        $hydrator = new InputHydrator($input, $module, $routeName, $skipQueries);

        return $hydrator->hydrate();
    }
}

if (! function_exists('hydrate_input_type')) {
    function hydrate_input_type(array $input)
    {
        $inputTypes = modularityConfig('input_types', []);

        if (array_key_exists($input['type'], $inputTypes)) {
            return array_merge($inputTypes[$input['type']], Arr::except($input, ['type']));
        }

        return $input;
    }
}

if (! function_exists('modularity_format_input')) {
    function modularity_format_input(array $input, $module = null, $routeName = null, $skipQueries = null)
    {
        $defaultInput = modularity_default_input();

        return configure_input(hydrate_input(array_merge($defaultInput, $input), $module, $routeName, $skipQueries));
    }
}

if (! function_exists('modularity_format_inputs')) {
    function modularity_format_inputs(array $inputs, $module = null, $routeName = null, $skipQueries = null)
    {
        return array_map(function ($v) use ($module, $routeName, $skipQueries) {
            return modularity_format_input($v, $module, $routeName, $skipQueries);
        }, $inputs);
    }
}
