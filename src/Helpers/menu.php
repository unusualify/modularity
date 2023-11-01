<?php

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

if (!function_exists('unusualBaseMenu')) {

    /**
     * @param
     * @return array
     */
    function unusualBaseMenu()
    {
        return makeSidebarMenu(unusualConfig('system_modules', []));
    }
}
if (!function_exists('unusualModulesMenu')) {

    /**
     * @param string $file
     * @return array
     */
    function unusualModulesMenu()
    {
        $configs = array_map(
            function($item){
                return config(snakeCase($item->getName()));
            },
            array_filter(Module::all(), function($item){
                return $item->getName() != 'Base' && $item->isStatus(true) && config(snakeCase($item->getName()));
            })
        );

        return makeSidebarMenu($configs);

    }
}
if (!function_exists('makeSidebarMenu')) {

    /**
     * @param string $file
     * @return array
     */
    function makeSidebarMenu($configs)
    {
        $arrays = [];

        foreach ( $configs as $moduleName => $config) {
            try {
                $name = $config['name'];
                //code...
            } catch (\Throwable $th) {
                // continue;
                dd(
                    $moduleName,
                    $config,
                    $configs,
                );
            }

            // $pr => parent route
            // $sr => sub route
            $pr = findParentRoute($config); //  parent_route array|object
            $array = [];

            $number_route = count($config['routes']);

            foreach( $config['routes'] as $item){
                // $sr sub route array|object
                $route_name = $item['route_name'] . ".index";

                if( !(isset($item['parent']) && $item['parent']) ){
                    $route_name = $pr['route_name'] . '.' . $route_name;
                }

                if(isset($config['base_prefix']) && $config['base_prefix'])
                    $route_name = snakeCase(unusualConfig('name')) . "." . $route_name;

                if( isset($item['parent']) && $item['parent'] ){
                    if($number_route < 2 && Route::has($route_name)){
                        $array = [
                            'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                            'icon' => $item['icon'] ?? '',
                            'route' => route( $route_name )
                        ];
                    }else{
                        $array = [
                            'name' => $config['headline'] ?? pluralize(headline($config['name'])),
                            'icon' => $config['icon'] ?? '',
                            'items' => [
                                [
                                    'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                                    'icon' => $item['icon'] ?? '',
                                    'route' => route( $route_name )
                                ]
                            ]
                        ];
                    }
                }else{
                    $array['items'][] = [
                        'name' => $item['headline'] ?? pluralize($item['name']),
                        'icon' => $item['icon'] ?? '',
                        'route' => route( $route_name )
                    ];
                }
            }
            $arrays[] = $array;
        }

        return $arrays;
    }

}
if (!function_exists('makeSidebarMenuItem')) {

    /**
     * @param string $file
     * @return array
     */
    function makeSidebarMenuItem($array)
    {
        if(isset($array['items']))
            $array['items'] = array_map(function($item){
                return makeSidebarMenuItem($item);
            }, $array['items']);

        if(isset($array['route_name'])){
            // if(Route::has($array['route_name'])){
                $array['route'] = route($array['route_name']);
            // }
        }
        unset($array['route_name']);

        return array_merge_recursive_preserve([
            'is_active' => 0,
            'icon' => ''
        ], $array);

    }
}
if (!function_exists('setActiveMenuItem')) {

    /**
     * @param string $file
     * @return array
     */
    function setActiveMenuItem(&$items, $url)
    {
        foreach ($items as $key => &$item) {
            if(isset($item['route']) && $url == $item['route']){
                $item['is_active'] = 1;
                return true;
            }else if(isset($item['items'])){
                if( setActiveMenuItem($item['items'], $url)){
                    $item['is_active'] = 1;
                    return true;
                }
            }
        };

        return false;
    }
}
