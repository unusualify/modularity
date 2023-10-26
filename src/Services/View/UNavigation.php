<?php

namespace OoBook\CRM\Base\Services\View;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use OoBook\CRM\Base\Traits\ManageNames;

class UNavigation
{
    use ManageNames;

    protected $request;

    protected $types = [
        'default',
        'superadmin',
        'client',
    ];

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

    }

    public function baseMenu()
    {
        return $this->sidebarMenuFromConfigs(unusualConfig('internal_modules', []));
    }

    public function modulesMenu()
    {
        return $this->sidebarMenuFromConfigs(array_map(
            function($item){
                return config(snakeCase($item->getName()));
            },
            array_filter(Module::all(), function($item){
                return $item->getName() != 'Base' && $item->isStatus(true) && config(snakeCase($item->getName()));
            })
        ));
    }

    public function sidebarMenuItem($array)
    {
        $is_active = 0;
        if(isset($array['items']))
            $array['items'] = array_map(function($item){
                return $this->sidebarMenuItem($item);
            }, $array['items']);

        if(isset($array['route_name'])){
            if(Route::has($array['route_name'])){
                $array['route'] = route($array['route_name']);
                if($array['route'] == $this->request->url()){
                    $is_active = 1;
                }
            }else{
                return false;
            }
        }
        unset($array['route_name']);

        return array_merge_recursive_preserve([
            'is_active' => $is_active,
            'icon' => ''
        ], $array);
    }

    public function sidebarMenuFromConfigs($configs)
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



            $pr_name = $this->getSnakeCase($name);
            // $pr => parent route
            // $sr => sub route
            $pr = findParentRoute($config)?:[
                'url' => pluralize(kebabCase($config['name'])),
                'route_name' => snakeCase($config['name'])
            ]; //  parent_route array|object

            if( !array_key_exists('url', $pr) && !array_key_exists('route_name', $pr) ){
                $pr['url'] = pluralize(kebabCase($config['name']));
                $pr['route_name'] = snakeCase($config['name']);
            }
            $number_route = count($config['routes']);

            $array = [];
            if($number_route > 0){
                $array = [
                    'name' => $config['headline'] ?? pluralize(headline($config['name'])),
                    'icon' => $config['icon'] ?? '',
                ];
            }

            $system_route_prefix = (isset($config['base_prefix']) && $config['base_prefix']) ? snakeCase(studlyName(unusualConfig('base_prefix', 'system-settings'))) . '.' : '';

            foreach( $config['routes'] as $item){
                // $sr sub route array|object
                $route_name = $item['route_name'] . ".index";
                // dd($route_name);

                if( !(isset($item['parent']) && $item['parent']) ){
                    try {

                        $route_name = $pr_name . '.' . $route_name;
                    } catch (\Throwable $th) {
                        dd(
                            $pr,
                            findParentRoute($config),
                            $config
                        );
                    }
                    // $route_name = $pr['route_name'] . '.' . $route_name;

                }

                $route_name = $system_route_prefix . $route_name;

                if( isset($item['parent']) && $item['parent'] ){

                    // only one link for module
                    if($number_route < 2 && Route::has($route_name)){
                        $array['route_name'] = $route_name;
                        // $array = [
                        //     'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                        //     'icon' => $item['icon'] ?? '',
                        //     'route_name' => $route_name
                        // ];
                    }else{

                        $array['items'][$this->getSnakeCase($item['name'])] = [
                            'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                            'icon' => $item['icon'] ?? '',
                            'route_name' => $route_name
                        ];
                        // $array = [
                        //     'name' => $config['headline'] ?? pluralize(headline($config['name'])),
                        //     'icon' => $config['icon'] ?? '',
                        //     'items' => [
                        //         snakeCase($config['name']) => [
                        //             'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                        //             'icon' => $item['icon'] ?? '',
                        //             'route_name' => $route_name
                        //         ]
                        //     ]
                        // ];
                    }
                }else{
                    $array['items'][$this->getSnakeCase($item['name'])] = [
                        'name' => $item['headline'] ?? pluralize($item['name']),
                        'icon' => $item['icon'] ?? '',
                        'route_name' => $route_name
                    ];
                }
            }

            // dd($array);
            $arrays[snakeCase($config['name'])] = $array;
        }
        return $arrays;
    }

    /**
     * @param array $items
     * @param string $url
     * @return array
     */
    function setActiveSidebarMenuItem(&$items, $url)
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

    public function formatSidebarMenus(&$array)
    {
        // types default superadmin client
        foreach ($this->types as $type) {
            if(isset($array[$type])){
                $this->formatSidebarMenu($array[$type]);
                $this->setActiveSidebarItems($array[$type]);
            }
        }

        return $array;
    }

    public function formatSidebarMenu(&$array)
    {
        $this->unsetMenuKeys($array);

        foreach ($array as $key => $item) {
            // this checking is important for not mischoosing keys of array and sidebar array configuration
            if(array_key_exists('name', $item) && is_string($item['name']) ){
                if(($res = $this->sidebarMenuItem($item))){
                    $array[$key] = $res ;
                }else{
                    unset($array[$key]);
                }
            }else{
                $this->formatSidebarMenu($item);
            }
        }

        // return $array;
    }

    public function unsetMenuKeys(&$array)
    {
        if(is_array($array) && !array_key_exists('name', $array) ){
            $array = array_values($array);
            foreach($array as $key => $conf){
                $array[$key] = $this->unsetMenuKeys($conf);
            }
        }else if(is_array($array) && array_key_exists('name', $array) && array_key_exists('items', $array)){
            $this->unsetMenuKeys($array['items']);
        }

        return $array;
    }

    public function setActiveSidebarItems(&$items)
    {
        foreach ($items as $key => &$item) {
            if(isset($item['route']) && $this->request->url() == $item['route']){
                $item['is_active'] = 1;
                return true;
            }else if(isset($item['items'])){
                if( $this->setActiveSidebarItems($item['items'])){
                    $item['is_active'] = 1;
                    return true;
                }
            }
        };

        return false;
    }
}
