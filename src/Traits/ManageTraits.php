<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
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

            return $this->chunkInputs($route_config['inputs']);
            // return $route_config['inputs'];
        }

        return [];
    }

    public function chunkInputs($schema, $all = false) {
        return Arr::mapWithKeys($schema, function($input, $key) use($all){
            if(isset($input['type'])){
                switch ($input['type']) {
                    case 'group':
                    case 'wrap':
                        return $this->chunkInputs($input['schema'] ?? []);
                    break;
                    case 'morphTo':
                        return [ uniqid() => $all ? $this->chunkInputs($input['schema']) :$input];
                    break;
                    default:

                        break;
                }

                if(isset($input['name'])){
                    return [ $input['name'] => $input ];
                }
            }
            return [];
        });
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

        return ($routeName && $repositoryClass = UFinder::getRouteRepository($routeName)) ? App::make($repositoryClass)?->getModel() : null;
    }

    public function prepareFieldsBeforeSaveManageTraits($object, $fields) {

        if(isset($fields['password'])){
            $fields['password'] = Hash::make($fields['password']);
        }

        return $fields;
    }
}
