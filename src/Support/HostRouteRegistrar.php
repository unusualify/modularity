<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Traits\ManageNames;
use BadMethodCallException;
class HostRouteRegistrar{
    use ManageNames;


    private $hostClass;
    private $options;
    private $router;

    private $callables = [
        'host',
        'group',
    ];


    private $allowedAttributes = [
        'middleware',
        'name',
    ];

    protected $aliases = [
        'name' => 'as',
        'scopeBindings' => 'scope_bindings',
        'withoutMiddleware' => 'excluded_middleware',
    ];


    public function __construct(
        private Application $app,
        private string $baseHostName,
    )
    {
    }

    public function group($callback){
        return Route::group($this->options, $callback);
    }

    private function host($models){
        $this->setModel($models)->setHostingOptions();
        return $this;
    }

    private function setHostingOptions(){

        $model = $this->getHostModel();

        $groupOptions = [];
        $prefixes = [];
        $middleware = [];

        if($model){
            $groupOptions['domain'] = $model->url;
            $prefixes = $model->hostableChildRouteParameters();
        }else{
            $groupOptions['domain'] = $this->getBaseHostName();
            $prefixes = array_map(function($class){
                return $class::hostableRouteBindingParameter();
            }, $this->hostableClasses,);
        }
        $groupOptions['prefix'] = implode('/', $prefixes);
        $groupOptions['middleware'] = ['hostable'];

        /**
         * host başta kullanılmadıysa middlewareleri eklemece
         */

        $this->options = $groupOptions;

        return $this;



    }

    private function attributes($key, $value){
        if(!in_array($key, $this->allowedAttributes)) return;

        if($key === 'middleware'){
            foreach ($value as $index => $middleware) {
                $value[$index] = $middleware;
            }
        }
        $attributeKey = Arr::get($this->aliases, $key, $key);

        $this->options[$attributeKey] = is_array($value) ? $value[0] : $value;

        return $this;
    }

    private function getBaseHostName() : string
    {
        return $this->baseHostName;
    }

    private function getHostModel(){
        return $this->hostClass;
    }

    private function setModel($model){
        $this->hostableClasses = is_array($model) ? $model : [$model];
        $this->setHostModel();
        return $this;
    }

    private function setHostModel(){
        $this->hostClass = $this->combineHostableClasses()
        ->where(fn($hostable) => $hostable->url == $this->app['request']->getHost())
        ->first();

        return $this;
    }


    private function combineHostableClasses() : Collection
    {

        if(!$this->areClassesHostable()) return Collection::make([]);

        return array_reduce($this->hostableClasses,function($carry, $class){
            $carry = $carry->merge($class::hostables());

            return $carry;
        } ,Collection::make([]));

    }


    private function areClassesHostable() : bool {
        foreach ($this->hostableClasses as $key => $model) {
            if(!Schema::hasTable(App::make($model)->getTable())){
                return false;
            }
        }
        return true;
    }


    public function getRouteArguments()
    {
        $model = $this->getHostModel();

        return array_merge($model ? $model->hostableRouteArguments() : [], $this->app['request']->route()->parameters());
    }

    public function getRouteParameters()
    {
        return $this->app['request']->route()->parameters();
    }

    public function __call($method, $arguments)
    {


        if(in_array($method, $this->allowedAttributes)){
            return  $this->attributes($method, $arguments);
        };
        if(in_array($method, $this->callables)){
            return $this->{$method}($arguments);
        }
        throw new BadMethodCallException(
            sprintf('Method %s::%s does not exists in callable methods or allowed attributes list', static::class, $method)
        );
    }
}
