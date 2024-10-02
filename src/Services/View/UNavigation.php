<?php

namespace Unusualify\Modularity\Services\View;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Traits\ManageNames;

class UNavigation
{
    use ManageNames;

    protected $request;

    protected $types = [
        'default',
        'superadmin',
        'client',
        'guest',
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

    public function systemMenu()
    {
        return $this->sidebarMenuFromModules(Modularity::getSystemModules());
        // return [];

    }

    public function modulesMenu()
    {
        return $this->sidebarMenuFromModules(Modularity::getModules());
    }

    public function sidebarMenuItem($array)
    {
        $is_active = 0;
        if (isset($array['items'])) {
            $array['items'] = array_map(function ($item) {
                return $this->sidebarMenuItem($item);
            }, $array['items']);
        }
        if (isset($array['menuItems'])) {
            $array['menuItems'] = array_map(function ($item) {
                return $this->sidebarMenuItem($item);
            }, $array['menuItems']);
        }

        if (isset($array['route_name'])) {
            $routeName = Route::hasAdmin($array['route_name']);
            if ($routeName) {
                $array['route'] = route($routeName);
                if ($array['route'] == $this->request->url()) {
                    $is_active = 1;
                }
            } else {
                return false;
            }
        }
        unset($array['route_name']);

        return array_merge_recursive_preserve([
            'is_active' => $is_active,
            'icon' => '',
        ], $array);
    }

    public function sidebarMenuFromModules($modules)
    {
        $arrays = [];

        foreach ($modules as $moduleName => $module) {
            // $pr => parent route
            // $sr => sub route

            $name = $module->getName();
            $config = $module->getConfig();
            $pr_name = $module->getSnakeName();

            $pr = $module->getParentRoute() ?: [
                'url' => pluralize(kebabCase($name)),
                'route_name' => snakeCase($name),
            ]; //  parent_route array|object

            // if( !array_key_exists('url', $pr) && !array_key_exists('route_name', $pr) ){
            //     $pr['url'] = pluralize(kebabCase($config['name']));
            //     $pr['route_name'] = snakeCase($config['name']);
            // }
            $routes = $module->getRouteConfigs(valid: true);
            $number_route = count($routes);

            $array = [];
            if ($number_route > 0) {
                $array = [
                    'name' => $config['headline'] ?? pluralize(headline($name)),
                    'icon' => $config['icon'] ?? '',
                ];
            }

            $route_prefix = adminRouteNamePrefix() ? adminRouteNamePrefix() . '.' : '';

            $route_prefix .= $module->hasSystemPrefix()
                ? systemRouteNamePrefix() . '.'
                : '';

            foreach ($routes as $item) {

                // if(!isset($item['name']) ){
                //     continue;
                // }

                // $sr sub route array|object
                $route_name = ($item['route_name'] ?? snakeCase($item['name'])) . '.index';
                // dd($route_name);

                if (! (isset($item['parent']) && $item['parent'])) {
                    try {
                        $route_name = $pr_name . '.' . $route_name;
                    } catch (\Throwable $th) {
                        dd(
                            $pr,
                            $module->getConfig(),
                            $module->getParentRoute()
                        );
                    }
                }

                $route_name = $route_prefix . $route_name;

                if (isset($item['parent']) && $item['parent']) {
                    // only one link for module
                    if ($number_route < 2 && Route::has($route_name)) {
                        $array['route_name'] = $route_name;
                    } else {

                        $array['items'][$this->getSnakeCase($item['name'])] = [
                            'name' => $item['headline'] ?? pluralize(headline($item['name'])),
                            'icon' => $item['icon'] ?? '',
                            'route_name' => $route_name,
                        ];
                    }
                } else {
                    $array['items'][$this->getSnakeCase($item['name'])] = [
                        'name' => $item['headline'] ?? pluralize($item['name']),
                        'icon' => $item['icon'] ?? '',
                        'route_name' => $route_name,
                    ];
                }
            }

            if (count($array) > 0) {
                $arrays[$module->getSnakeName()] = $array;
            }

        }

        return $arrays;

    }

    public function formatSidebarMenus(&$array)
    {
        // types default superadmin client
        foreach ($this->types as $type) {
            if (isset($array[$type])) {
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
            if (array_key_exists('name', $item) && is_string($item['name'])) {
                if (($res = $this->sidebarMenuItem($item))) {
                    $array[$key] = $res;
                } else {
                    unset($array[$key]);
                }
            } else {
                $this->formatSidebarMenu($item);
            }
        }

        // return $array;
    }

    public function unsetMenuKeys(&$array)
    {
        if (is_array($array) && ! array_key_exists('name', $array)) {
            $array = array_values($array);
            foreach ($array as $key => $conf) {
                $array[$key] = $this->unsetMenuKeys($conf);
            }
        } elseif (is_array($array) && array_key_exists('name', $array) && array_key_exists('items', $array)) {
            $this->unsetMenuKeys($array['items']);
        }

        return $array;
    }

    public function setActiveSidebarItems(&$items)
    {
        foreach ($items as $key => &$item) {
            if (isset($item['route']) && $this->request->url() == $item['route']) {
                $item['is_active'] = 1;

                return true;
            } elseif (isset($item['items'])) {
                if ($this->setActiveSidebarItems($item['items'])) {
                    $item['is_active'] = 1;

                    return true;
                }
            } elseif (isset($item['menuItems'])) {
                if ($this->setActiveSidebarItems($item['menuItems'])) {
                    $item['is_active'] = 1;

                    return true;
                }
            }
        }

        return false;
    }
}
