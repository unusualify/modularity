<?php
namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Facades\UFinder;

trait ManageModuleRoute {

    public function moduleName()
    {

        if( preg_match('/[M|m]{1}odules[\/\\\]([A-Za-z]+)[\/\\\]/', get_class($this), $matches)){
            return $matches[1];
        }

        return null;
    }

    public function routeName()
    {
        $moduleName = $this->moduleName();

        $routeName = null;

        if(!$moduleName)
            return $routeName;
        if( preg_match('/(\w+)(?=(Request|Repository|Controller))/', get_class_short_name($this), $matches)){
            $routeName = studlyName($matches[1]);
        } else if(preg_match('/(\w+)\Entities/', get_class($this), $matches)){
            $routeName = studlyName(get_class_short_name($this));
        }

        return $routeName;
    }

    public function routeConfig()
    {
        $moduleName = $this->moduleName();

        $routeName = $this->routeName();

        if( $moduleName && $routeName){
            $module = Modularity::find($moduleName);

            return $module->getRouteConfig($routeName);
        }

        return [];
    }


    public function getRouteTitleColumnKey() {

        return !empty($conf = $this->routeConfig()) ? ($conf['title_column_key'] ?? 'name') : 'name';
    }

    public function getRouteInputs() {

        return !empty($conf = $this->routeConfig()) ? ($conf['inputs'] ?? []) : [];
    }

    public function getRouteHeaders() {

        return !empty($conf = $this->routeConfig()) ? ($conf['headers'] ?? []) : [];
    }

    public function getRouteTableOptions() {

        return !empty($conf = $this->routeConfig()) ? ($conf['table_options'] ?? []) : [];
    }
}
