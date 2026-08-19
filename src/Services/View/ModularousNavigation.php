<?php

namespace Unusualify\Modularous\Services\View;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\Traits\Allowable;
use Unusualify\Modularous\Traits\ManageNames;
use Unusualify\Modularous\Traits\ResponsiveVisibility;

class ModularousNavigation
{
    use ManageNames, Allowable, ResponsiveVisibility;

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
        return $this->sidebarMenuFromModules(Modularous::getSystemModules());
        // return [];

    }

    public function modulesMenu()
    {
        return $this->sidebarMenuFromModules(Modularous::getModules());
    }

    public function sidebarMenuItem($array)
    {
        $is_active = 0;

        if (isset($array['can']) || isset($array['allowedRoles'])) {
            $user = auth()->user();

            $result = false;

            if (isset($array['can']) && is_string($array['can'])) {
                $result = $user->can($array['can']);
            }

            if (isset($array['allowedRoles'])) {
                $result = $this->isAllowedItem(
                    $array,
                    searchKey: 'allowedRoles',
                    disallowIfUnauthenticated: true
                );
            }

            if (! $result) {
                return false;
            }
        }

        if (isset($array['items'])) {
            $array['items'] = array_reduce($array['items'], function ($carry, $item) {
                $res = $this->sidebarMenuItem($item);
                if ($res) {
                    $carry[] = $res;
                }

                return $carry;
            }, []);
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
                $array['is_modularous_route'] = Modularous::isModularousRoute($routeName);
                if ($array['route'] == $this->request->url()) {
                    $is_active = 1;
                }
            } else {
                return false;
            }
        }
        unset($array['route_name']);

        if (isset($array['connector'])) {
            $connector = new Connector($array['connector']);

            if (isset($metric['pushEvents'])) {
                $connector->pushEvents($metric['pushEvents']);
            }

            $connector->run($array, 'badge');
        } elseif (isset($array['badge']) && is_callable($array['badge'])) {
            $array['badge'] = $array['badge']();
        }

        if (isset($array['badge'])) {
            if ($array['badge'] < 1) {
                unset($array['badge']);
            } else {
                $array['badge'] = (int) $array['badge'];
                $array['badgeProps'] = array_merge([
                    'color' => 'secondary',
                    'class' => 'text-white',
                ], $array['badgeProps'] ?? []);
                $array['iconProps'] = array_merge([
                    'color' => 'secondary',
                ], $array['iconProps'] ?? []);
                $array['class'] = $array['classOnBadge'] ?? 'text-secondary';
            }
        }

        if (isset($array['responsive'])) {
            $array = $this->applyResponsiveClasses($array, 'responsive');
        }

        return array_merge_recursive_preserve([
            'is_active' => $is_active,
            'icon' => 'mdi-note-outline',
        ], $array);
    }

    public function sidebarMenuFromModules($modules)
    {
        $arrays = [];

        foreach ($modules as $moduleName => $module) {
            $name = $module->getName();
            $config = $module->getRawConfig();
            $parentModuleRouteName = $module->getSnakeName();

            $parentModuleRoute = $module->getParentRoute() ?: [
                'url' => pluralize(kebabCase($name)),
                'route_name' => snakeCase($name),
            ]; //  parent_route array|object

            // if( !array_key_exists('url', $pr) && !array_key_exists('route_name', $pr) ){
            //     $pr['url'] = pluralize(kebabCase($config['name']));
            //     $pr['route_name'] = snakeCase($config['name']);
            // }
            // Hot-path ADR: raw config arrays only — never sidebarRoutes()/moduleRoute()
            // (those build ModuleRouteRegistry + FeatureDetector and cost ~0.5–1s on admin docs).
            $moduleRoutes = $module->getRawRouteConfigs(null, true) ?: [];
            $numberOfRoutes = count($moduleRoutes);
            $array = [];
            if ($numberOfRoutes > 0) {
                $array = [
                    'name' => $config['headline'] ?? pluralize(headline($name)),
                    'icon' => $config['icon'] ?? '$modules',
                ];
            }

            $routePrefix = adminRouteNamePrefix() ? adminRouteNamePrefix() . '.' : '';

            $routePrefix .= $module->hasSystemPrefix()
                ? systemRouteNamePrefix() . '.'
                : '';

            foreach ($moduleRoutes as $key => $moduleRoute) {
                $moduleRouteName = is_array($moduleRoute)
                    ? ($moduleRoute['name'] ?? (is_string($key) ? $key : null))
                    : null;
                if (! is_string($moduleRouteName) || $moduleRouteName === '') {
                    continue;
                }

                $isParentModuleRoute = (bool) ($moduleRoute['parent'] ?? false);
                $isSingularModuleRoute = $module->isSingleton($moduleRouteName);
                $routeName = snakeCase($moduleRouteName);

                if ($isSingularModuleRoute) {
                    $routeName = $routeName . '.edit';
                } else {
                    $routeName = $routeName . '.index';
                }

                if (! $isParentModuleRoute) {
                    try {
                        $routeName = $parentModuleRouteName . '.' . $routeName;
                    } catch (\Throwable $th) {
                        dd(
                            $parentModuleRoute,
                            $module->getRawConfig(),
                            $module->getParentRoute()
                        );
                    }
                }

                $routeName = $routePrefix . $routeName;
                $headline = $moduleRoute['headline']
                    ?? $moduleRoute['label']
                    ?? $moduleRoute['title']
                    ?? pluralize(headline($moduleRouteName));

                if ($isParentModuleRoute) {
                    // only one link for module
                    if ($numberOfRoutes < 2 && Route::has($routeName)) {
                        $array['route_name'] = $routeName;
                    } else {
                        $array['items'][$this->getSnakeCase($moduleRouteName)] = [
                            'name' => $headline,
                            'icon' => $moduleRoute['icon'] ?? '$submodule',
                            'route_name' => $routeName,
                        ];
                    }
                } else {
                    $array['items'][$this->getSnakeCase($moduleRouteName)] = [
                        'name' => $headline,
                        'icon' => $moduleRoute['icon'] ?? '$submodule',
                        'route_name' => $routeName,
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
                $array[$type] = $this->formatSidebarMenu($array[$type]);
                // $this->setActiveSidebarItems($array[$type]);
            }
        }

        return $array;
    }

    public function formatSidebarMenu($array)
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
                $array[$key] = $this->formatSidebarMenu($item);
            }
        }

        $this->setActiveSidebarItems($array);

        return $array;
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
        $result = false;
        foreach ($items as $key => &$item) {
            $itemActive = false;
            if (isset($item['route']) && $this->request->url() == $item['route']) {
                $item['is_active'] = 1;

                $itemActive = true;
                $result = true;
            } elseif (isset($item['items']) || isset($item['menuItems'])) {
                $itemsKey = isset($item['items']) ? 'items' : 'menuItems';
                if ($this->setActiveSidebarItems($item[$itemsKey])) {
                    $item['is_active'] = 1;

                    $itemActive = true;
                    $result = true;
                }
            }

            if ($itemActive && isset($item['badge']) && is_numeric($item['badge'])) {
                $item['badgeProps'] = [
                    'color' => 'white',
                    'class' => 'primary',
                ];
                $item['iconProps'] = [];
                unset($item['class']);
            }
        }

        return $result;
    }
}
