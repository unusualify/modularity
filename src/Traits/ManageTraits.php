<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UFinder;

trait ManageTraits {

    /**
     * @param string|null $method
     * @return array
     */
    protected function traitsMethods(string $method = null)
    {
        $method = $method ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $methods = array_map(function (string $trait) use ($method) {
            return $method . $trait;
        }, $uniqueTraits);

        return array_filter($methods, function (string $method) {
            return method_exists(get_called_class(), $method);
        });
    }

    public function inputs() {
        $moduleName = $this->moduleName();
        $routeName = $this->routeName();

        if( $moduleName && $routeName){
            $module = Modularity::find($moduleName);
            $route_config = $module->getRouteConfig($routeName);

            return $route_config['inputs'];
        }

        return [];
    }

    public function routeName() {
        $moduleName = $this->moduleName();

        $routeName = null;

        if(!$moduleName)
            return $routeName;
        if( preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)){
            $routeName = studlyName($matches[1]);
        }

        return $routeName;
    }

    public function moduleName() {

        if( preg_match('/[M|m]{1}odules[\/\\\]([A-Za-z]+)[\/\\\]/', get_class($this), $matches)){
            return $matches[1];
        }

        return null;
    }

    public function model() {
        $routeName = $this->routeName();

        return $routeName  ? App::make(UFinder::getRouteRepository($routeName))?->getModel() : null;
    }


}
