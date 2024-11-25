<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait ManageModuleRoute
{
    public $item = [];

    public function moduleName()
    {

        if (preg_match('/[M|m]{1}odules[\/\\\]([A-Za-z]+)[\/\\\]/', get_class($this), $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function routeName()
    {
        $moduleName = $this->moduleName();

        $routeName = null;

        if (! $moduleName) {
            return $routeName;
        }
        if (preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)) {
            $routeName = studlyName($matches[1]);
        } elseif (preg_match('/(\w+)\Entities/', get_class($this), $matches)) {
            $routeName = studlyName(get_class_short_name($this));
        }

        return $routeName;
    }

    public function routeConfig()
    {
        $moduleName = $this->moduleName();

        $routeName = $this->routeName();

        if ($moduleName && $routeName) {
            $module = Modularity::find($moduleName);

            // dd($module->getRouteConfig($routeName));
            return $module->getRouteConfig($routeName);
        }

        return [];
    }

    public function getRouteTitleColumnKey()
    {

        return ! empty($conf = $this->routeConfig()) ? ($conf['title_column_key'] ?? 'name') : 'name';
    }

    public function getRouteInputs()
    {

        return ! empty($conf = $this->routeConfig()) ? ($conf['inputs'] ?? []) : [];
    }

    public function getRouteHeaders()
    {

        return ! empty($conf = $this->routeConfig()) ? ($conf['headers'] ?? []) : [];
    }

    public function getRouteTableOptions()
    {

        return ! empty($conf = $this->routeConfig()) ? ($conf['table_options'] ?? []) : [];
    }

    public function initConnector($connector){
        $targetType = 'uri';

        $moduleInfo = $this->findModuleAndRoute($connector);
        $targetType = $this->findTarget($moduleInfo);
        $data = $this->execTarget($targetType);

        return $data;
    }

    public function findModuleAndRoute($connector){

        $parts = explode('|', $connector);

        $names = explode(':', array_shift($parts)); // moduleName:routeName

        $routeName = $this->getStudlyName(array_pop($names));
        $targetModuleName = $this->getStudlyName(! empty($names) ? array_pop($names) : $this->moduleName);
        $targetModule = Modularity::find($targetModuleName);

        return [
            'module' => $targetModule,
            'route' => $routeName,
            'parts' => $parts,
        ];

    }

    public function findTarget($moduleInfo){
        $targetType = 'uri'; // Default target type

        $types = ! empty($moduleInfo['parts']) ? explode(':', array_shift($moduleInfo['parts'])) : ['uri', 'index']; //uri:edit
        $targetType = array_shift($types) ?? $targetType;

        $item = [];

        switch ($targetType) {
            case 'uri':
                $item['endpoint'] = $moduleInfo['module']->getRouteActionUri($moduleInfo['route'], empty($types) ? 'index' : array_shift($types));
                break;
            default:
                $item[kebabCase($targetType)] = implode(':', [$moduleInfo['module']->getRouteClass($moduleInfo['route'], $targetType), ...$types]);
                break;
        }

        $item['module'] = $moduleInfo['module'];
        $item['route'] = $moduleInfo['route'];
        return $item;
    }


    public function execTarget($item){

        if (isset($item['repository'])) {

            $args = explode(':', $item['repository']);

            $className = array_shift($args);
            $methodName = array_shift($args) ?? 'list';

            if (! @class_exists($className)) {
                return $item;
            }

            $repository = App::make($className);

            $params = Collection::make($args)->mapWithKeys(function ($arg) {

                [$name, $value] = explode('=', $arg);

                return [$name => explode(',', $value)];
            })->toArray();

            $items = call_user_func_array([$repository, $methodName], [
                ...($methodName == 'list' ? ['column' => [$item['itemTitle'] ?? 'name', ...$this->getItemColumns()]] : []),
                ...$params,
            ]);

            $item['items'] = $items;

            if (is_array($item['items']) && count($item['items']) > 0) {
                if (! isset($item['items'][0]['name'])) {
                    $item['itemTitle'] = array_keys(Arr::except($item['items'][0], ['name']))[0];
                }
            }
        }
        $item['repository'] = $repository;

        return $item;

    }

    protected function getWiths()
    {
        $item = $this->item;

        $withs = [];

        if (isset($item['cascades'])) {
            $withs = $item['cascades'];
        }

        $withs = array_merge($withs, $this->withs());

        return $withs;
    }

    public function withs(): array
    {
        return [];
    }

    protected function getItemColumns()
    {
        $item = $this->item;

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
        $columns = array_merge($columns, $this->itemColumns());

        return $columns;
    }

    public function itemColumns(): array
    {
        return [];
    }
}
