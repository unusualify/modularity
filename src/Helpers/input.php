<?php

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
    function hydrate_input(array $input, $module = null)
    {
        $hydrator = new InputHydrator($input, $module);

        return $hydrator->hydrate();
    }
}

if (! function_exists('modularity_format_input')) {
    function modularity_format_input(array $input)
    {
        $defaultInput = modularity_default_input();

        return configure_input(hydrate_input(array_merge($defaultInput, $input)));
    }
}

if (! function_exists('modularity_format_inputs')) {
    function modularity_format_inputs(array $inputs)
    {
        return array_map(function ($v) {
            return modularity_format_input($v);
        }, $inputs);
    }
}
