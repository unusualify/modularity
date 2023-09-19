<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
// use Module;


if (! function_exists('unusualBaseKey')) {
    function unusualBaseKey() {
        return \Illuminate\Support\Str::snake(env('BASE_NAME', 'Base'));
    }
}

if (! function_exists('getModule')) {
    function getModule($name = "Base") {
        // dd( app()['modules']->find( ucfirst( strtolower($name ) ) ) );
        // return app()['modules']->find( ucfirst( strtolower($name ) ) );
        return Module::find( studlyName($name )  );
    }
}

if (! function_exists('getCurrentModule')) {
    function getCurrentModule($file = null) {

        $module_name = getCurrentModuleName($file);

        $module = getModule($module_name);

        return $module;
    }
}

if (! function_exists('getCurrentModuleName')) {
    function getCurrentModuleName($file = null) {

        $dir = $file;

        if(!$file){

            $pattern = '/^((?![M|m]{1}odules\/Base).)*$/';
            $pattern = '/[M|m]{1}odules\/[A-Za-z]/';
            // dd($pattern);
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

if (! function_exists('getCurrentModuleStudlyName')) {
    function getCurrentModuleStudlyName($file = null) {
        // dd( getCurrentModule() );
        return getCurrentModule($file)->getStudlyName();

    }
}

if (! function_exists('getCurrentModuleLowerName')) {
    function getCurrentModuleLowerName($file = null) {
        return getCurrentModule($file)->getLowerName();
    }
}

if (! function_exists('getCurrentModuleSnakeName')) {
    function getCurrentModuleSnakeName($file = null) {
        return getCurrentModule($file)->getSnakeName();
    }
}

if (! function_exists('getCurrentModuleUrlName')) {
    function getCurrentModuleUrlName($file = null) {
        return pluralize( kebabCase(getCurrentModule($file)->getName()));
    }
}

if (! function_exists('getModuleSubRoutes')) {
    function getModuleSubRoutes($file = null) {
        return config(  getCurrentModule($file)->getLowerName().'.sub_routes' );
    }
}

if (! function_exists('getModuleSubRoute')) {
    function getModuleSubRoute($key, $file = null ) {
        return config(  getCurrentModule($file)->getLowerName().'.sub_routes.'.strtolower($key) );
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

        if(preg_match('/edit|show|update|destroy/', $action) && !array_key_exists($snakeName, $parameters) && !$singleton){
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
    function unusualConfig($notation = '', $default = '')
    {
        if($notation == '')
            return config(unusualBaseKey());
        else
            return config(unusualBaseKey() . '.' . $notation, $default);
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
