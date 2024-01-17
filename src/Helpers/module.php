<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;

/*
|--------------------------------------------------------------------------
| #curt is abbreviation of current
| #umod is abbreviation of unusualify/modularity
|--------------------------------------------------------------------------
*/

if (! function_exists('unusualBaseKey')) {
    function unusualBaseKey($notation = null) {
        $notation = !$notation ? $notation : "." . $notation;
        return \Illuminate\Support\Str::snake(env('UNUSUAL_BASE_NAME', 'Modularity')) . $notation;
    }
}

if (! function_exists('curtModule')) {
    function curtModule($file = null) {
        $name = curtModuleName($file);

        return Modularity::find( studlyName($name ));
    }
}

if (! function_exists('curtModuleName')) {
    function curtModuleName($file = null) {

        $dir = $file;

        if(!$file){

            $pattern = '/^((?![M|m]{1}odules\/Base).)*$/';
            $pattern = '/[M|m]{1}odules\/[A-Za-z]/';

            $dir = fileTrace($pattern);
        }

        // $pattern = '/(?<=\\/[M|m]{1}odules\/).*?(?=(\/|$))/';
        $pattern = '/(?<=[M|m]{1}odules[\/|\\\]).*?(?=(\/|\\\|$))/';

        preg_match($pattern, $dir, $matches);
        if(!count($matches)){
            dd($file, $matches, $dir, debug_backtrace());
        }

        return studlyName($matches[0]);

    }
}

if (! function_exists('curtModuleUrlPrefix')) {
    function curtModuleUrlPrefix($file = null) {
        // dd(
        //     curtModule($file)->prefix(),
        //     curtModule($file)->fullPrefix()
        // );
        return curtModule($file)->prefix();
        // return pluralize( kebabCase(curtModule($file)->getName()));
    }
}

if (! function_exists('curtModuleRouteNamePrefix')) {
    function curtModuleRouteNamePrefix($file = null) {
        return curtModule($file)->routeNamePrefix();
    }
}


if (! function_exists('curtModuleStudlyName')) {
    function curtModuleStudlyName($file = null) {
        // dd( curtModule() );
        return curtModule($file)->getStudlyName();

    }
}
if (! function_exists('curtModuleLowerName')) {
    function curtModuleLowerName($file = null) {
        return curtModule($file)->getLowerName();
    }
}
if (! function_exists('curtModuleSnakeName')) {
    function curtModuleSnakeName($file = null) {
        return curtModule($file)->getSnakeName();
    }
}

if (! function_exists('classUsesDeep')) {
    /**
     * @param mixed $class
     * @param bool $autoload
     * @return array
     */
    function classUsesDeep($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (! empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        }

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}

if (! function_exists('classHasTrait')) {
    /**
     * @param mixed $class
     * @param string $trait
     * @return bool
     */
    function classHasTrait($class, $trait)
    {
        $traits = classUsesDeep($class);

        if (in_array($trait, array_keys($traits))) {
            return true;
        }

        return false;
    }
}

if (!function_exists('moduleRoute')) {
    /**
     * @param string $moduleName
     * @param string $prefix
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function moduleRoute($moduleName, $prefix, $action = '', $parameters = [], $absolute = true, $singleton = false)
    {

        // Fix module name case
        $kebabName = kebabCase($moduleName);
        $snakeName = snakeCase($moduleName);

        // Nested module, pass in current parameters for deeply nested modules
        if (Str::contains($moduleName, '.')) {
            $parameters = array_merge(Route::current()->parameters(), $parameters);
        }

        // Create base route name
        // $routeName = 'admin.' . ($prefix ? $prefix . '.' : '');
        $routeName = (!!$prefix ? $prefix . '.' : '');

        // Prefix it with module name only if prefix doesn't contains it already
        if (
            config(unusualBaseKey() . '.allow_duplicates_on_route_names', false) ||
            ($prefix !== $moduleName &&
                !Str::endsWith($prefix, '.' . $moduleName))
        ) {
            $routeName .= "{$snakeName}";
        }
        // dd($snakeName, $parameters);
        if(preg_match('/edit|show|update|destroy|duplicate|restoreRevision|preview/', $action) && !array_key_exists($snakeName, $parameters) && !$singleton){
            $parameters[$snakeName] = ":id";
            // dd(
            //     $routeName,
            //     $parameters
            // );
        }

        //  Add the action name
        $routeName .= $action ? ".{$action}" : '';

        // dd(
        //     $routeName,
        //     $parameters,
        //     $absolute,
        //     route($routeName, $parameters, $absolute),
        //     $singleton
        // );
       // Build the route
        try {
            //code...
            return route($routeName, $parameters, $absolute);
        } catch (\Throwable $th) {
            dd(
                [
                    'throw' => $th,
                    'routeName' => $routeName,
                    'moduleName' => $moduleName,
                    'prefix' => $prefix,
                    'action' => $action,
                    'parameters' => $parameters,
                    'absolute' => $absolute
                ],
                debug_backtrace()
            );
            //throw $th;
        }
        return route($routeName, $parameters, $absolute);
    }
}

if (!function_exists('unusualRoute')) {
    /**
     * @param string $routeName
     * @param string $prefix
     * @param string $action
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function unusualRoute($route, $prefix, $action = '', $parameters = [], $absolute = true)
    {
        // Fix module name case
        $route = Str::camel($route);



        // Create base route name
        // $routeName = 'admin.' . ($prefix ? $prefix . '.' : '');
        $routeName = ($prefix ? $prefix . '.' : '');

        // Prefix it with module name only if prefix doesn't contains it already
        if (
            config(unusualBaseKey() . '.allow_duplicates_on_route_names', false) ||
            ($prefix !== $route &&
                !Str::endsWith($prefix, '.' . $route))
        ) {
            $routeName .= "{$route}";
        }


        //  Add the action name
        $routeName .= $action ? ".{$action}" : '';
        dd($routeName);
        // dd($routeName, $moduleName, $prefix);
        // Build the route
        return route($routeName, $parameters, $absolute);
    }
}

if (! function_exists('getUnusualTraits')) {
    /**
     * @return array
     */
    function getUnusualTraits()
    {
        return array_keys(Config::get(unusualBaseKey() . '.traits'));
        // return [
        //     // 'hasBlocks',
        //     'translationTrait',
        //     // 'hasSlug',
        //     'mediaTrait',
        //     'fileTrait',
        //     'positionTrait',
        //     // 'hasRevisions',
        //     // 'hasNesting',
        // ];
    }
}

if (! function_exists('activeUnusualTraits')) {
    /**
     * @return array
     */
    function activeUnusualTraits($traitOptions)
    {
        return Collection::make($traitOptions)
            ->only(getUnusualTraits())
            ->filter(function ($enabled) {
                return $enabled;
            });
    }
}

if (! function_exists('unusualTraitOptions')) {
    /**
     * @return array
     */
    function unusualTraitOptions()
    {
        return Collection::make(Config::get(unusualBaseKey() . '.traits'))->map(function ($trait, $key) {
                return [
                    $key,
                    $trait['command_option']['shortcut'] ?? null,
                    $trait['command_option']['input_type'] ?? InputOption::VALUE_NONE,
                    $trait['command_option']['description'] ?? '',
                ];
            })->values()->toArray();
    }
}

if (! function_exists('unusualConfig')) {
    /**
     * @return string|array
     */
    function unusualConfig($notation = null, $default = '')
    {
        if(!$notation)
            return config(unusualBaseKey());
        else
            return config(unusualBaseKey($notation), $default);
    }
}

if (! function_exists('findParentRoute')) {
    /**
     * @return string|array
     */
    function findParentRoute($config)
    {
        return array_values(array_filter($config['routes'], function($r){
            return isset($r['parent']) && $r['parent'];
        }))[0] ?? [];
    }
}

if (! function_exists('formatPermissionName')) {
    /**
     * @return string
     */
    function formatPermissionName($routeName, $permissionType)
    {
        // dd(
        //     Permission::get($permissionType)
        //     // Permission::cases(),
        //     // get_class_methods(Permission::class)
        //     // Permission::{$permissionType}->value
        // );
        return kebabCase($routeName) . "_" . Permission::get($permissionType);
    }
}

if (! function_exists('formatPermissionRecord')) {
    /**
     * @return array
     */
    function formatPermissionRecord($routeName, $permissionType, $guardName)
    {
        return ['name' => formatPermissionName($routeName, $permissionType), 'guard_name' => $guardName];
    }
}

if (! function_exists('routePermissionRecords')) {
    /**
     * @return array
     */
    function routePermissionRecords($routeName, $guardName, $cases = null)
    {
        return Arr::map($cases ?:Permission::cases(), function($item) use($routeName, $guardName){
            return ['name' => kebabCase($routeName) . '_' . $item->value, 'guard_name' => $guardName];
        });
    }
}

if (! function_exists('permissionRecordsFromRoutes')) {
    /**
     * @return array
     */
    function permissionRecordsFromRoutes($routes, $guardName)
    {
        $cases = Permission::cases();
        $records = [];

        foreach ($routes as $routeName) {
            $records = array_merge($records, routePermissionRecords($routeName, $guardName, $cases));
        }

        return $records;
    }
}
