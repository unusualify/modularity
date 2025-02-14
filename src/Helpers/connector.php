<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;
use Unusualify\Payable\Services\Paypal\Str;

if (! function_exists('init_connector')) {

    function init_connector($connector)
    {
        $targetType = 'uri';

        $moduleInfo = find_module_and_route($connector);

        if (! $moduleInfo['module']) {
            throw new \Exception('Module not found' . $connector);
        }

        $targetType = find_target($moduleInfo['module'], $moduleInfo['route'], get_connector_event($connector));

        $data = exec_target($targetType);

        return $data;
    }
}

if (! function_exists('find_module_and_route')) {

    function find_module_and_route($connector)
    {

        // $events = get_connector_event($connector);
        // dd($events);
        $names = find_module_route_names($connector); // moduleName:routeName
        // dd($names, $events);
        $routeName = Str::studly(array_pop($names));
        $targetModuleName = Str::studly(array_pop($names));
        $targetModule = Modularity::find($targetModuleName);

        // dd($events);
        return [
            'module' => $targetModule,
            'route' => $routeName,
            // 'events' => $events,
        ];

    }
}

if (! function_exists('find_module_route_names')) {
    function find_module_route_names($connector)
    {
        $parts = explode('|', $connector);
        // dd($parts);
        $names = array_shift($parts);

        // dd($names);
        return explode(':', $names);
    }
}

if (! function_exists('get_connector_event')) {
    function get_connector_event($connector)
    {
        // dd(explode('|', $connector));
        $parts = explode('|', $connector);
        // dd(explode('|',$connector));
        $events = $parts[1];

        // dd($events);
        return explode('|', $events);
    }
}

if (! function_exists('change_connector_event')) {
    function change_connector_event($event, $newEvent)
    {
        $event = $newEvent;

        return $event;
    }
}

if (! function_exists('find_target')) {

    function find_target(Module $moduleClass, string $routeName, $events)
    {
        $targetType = 'uri'; // Default target type
        // dd($events);
        $types = ! empty($events) ? explode(':', array_shift($events)) : ['uri', 'index']; // uri:edit
        // dd($types);
        $targetType = array_shift($types) ?? $targetType;

        $item = [];

        switch ($targetType) {
            case 'uri':
                $item['endpoint'] = $moduleClass->getRouteActionUri($routeName, empty($types) ? 'index' : array_shift($types));

                break;
            default:
                $item[kebabCase($targetType)] = implode(':', [$moduleClass->getRouteClass($routeName, $targetType), ...$types]);

                break;
        }

        $item['module'] = $moduleClass;
        $item['route'] = $routeName;

        // dd($item);
        return $item;
    }
}

if (! function_exists('exec_target')) {

    function exec_target($item)
    {
        if (isset($item['repository'])) {
            $args = explode(':', $item['repository']);
            // dd($args);

            $className = array_shift($args);
            $methodName = array_shift($args) ?? 'list';

            if (! @class_exists($className)) {
                return $item;
            }

            $repository = App::make($className);

            $params = Collection::make($args)->mapWithKeys(function ($arg) {

                [$name, $value] = explode('=', $arg);

                // if($name == 'columns')
                //     dd($name);
                return [$name => explode(',', $value)];
            })->toArray();

            $items = call_user_func_array([$repository, $methodName], [
                ...($methodName == 'list' ? ['column' => [$item['itemTitle'] ?? 'name', ...get_item_columns()]] : []),
                ...$params,
            ]);

            $item['items'] = $items;

            if (is_array($item['items']) && count($item['items']) > 0) {
                if (! isset($item['items'][0]['name'])) {
                    $item['itemTitle'] = array_keys(Arr::except($item['items'][0], ['name']))[0];
                }
            }
            $item['repository'] = $repository;

        }

        return $item;

    }
}

// if (! function_exists('get_withs')){
//     function get_withs()
//     {
//         $item = [''];

//         $withs = [];

//         if (isset($item['cascades'])) {
//             $withs = $item['cascades'];
//         }

//         $withs = array_merge($withs, withs());

//         return $withs;
//     }

// }

if (! function_exists('withs')) {

    function withs(): array
    {
        return [];
    }

}

if (! function_exists('get_item_columns')) {

    function get_item_columns()
    {
        $item = [];

        $columns = [];

        if (isset($item['ext'])) {
            $extensionMethods = $item['ext'];
            if (is_string($item['ext'])) {
                $extensionMethods = explode('|', $item['ext']);
            }

            $columns = array_merge(collect($extensionMethods)->filter(function ($pattern) {
                $args = $pattern;
                if (is_string($pattern)) {
                    $pattern = trim($pattern);
                    $args = explode(':', $pattern);
                }

                return in_array($args[0], ['lock']);
            })
                ->map(function ($pattern) {
                    $args = $pattern;
                    if (is_string($pattern)) {
                        $pattern = trim($pattern);
                        $args = explode(':', $pattern);
                    }

                    return $args[1];
                })
                ->toArray(), $columns);
        }
        $columns = array_merge($columns, item_columns());

        return $columns;
    }

}

if (! function_exists('item_columns')) {

    function item_columns(): array
    {
        return [];
    }

}
