<?php

namespace Unusualify\Modularity\Support;

use BadMethodCallException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Traits\ManageNames;

class HostRouting
{
    use ManageNames;

    protected $hostClass;

    protected $options;

    /**
     * Create new instance.
     *
     * @param string|null $schema
     */
    public function __construct(
        protected Application $app,
        protected string $baseHostName,
        public $hostableClasses = [],
        $options = [],
    )
    {
        $this->setOptions($options);

        $this->setHostModel();
    }

    public function getBaseHostName() : string
    {
        return $this->baseHostName;
    }

    public function setOptions($options = [])
    {
        if(isset($options['model'])){
            $this->model($options['model']);
        }

        $model = $this->getHostModel();

        $groupOptions = [];
        $prefixes = [];
        $middleware = [];

        if($model){
            $groupOptions['domain'] = $model->url;

            $prefixes = $model->hostableChildRouteParameters();
        }else{
            $groupOptions['domain'] = $this->getBaseHostName();

            $prefixes = array_map(function($class) {
                return $class::hostableRouteBindingParameter();
            }, $this->hostableClasses);
        }

        $groupOptions['prefix'] = implode('/', $prefixes);

        $groupOptions['middleware'] = ['hostable'];

        if(isset($options['middleware']) && ($middleware = $options['middleware'])){
            unset($options['middleware']);

            $groupOptions['middleware'] = array_merge($groupOptions['middleware'], Arr::except($middleware, ['hostable']));
        }

        $groupOptions = array_merge($groupOptions, Arr::except($options, ['middleware', 'model']));

        $this->options = $groupOptions;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setHostModel()
    {
        $this->hostClass = $this->combineHostModels()
            ->where(fn($hostable) => $hostable->url == $this->app['request']->getHost())
            ->first();

        return $this;
    }

    public function getHostModel()
    {
        return $this->hostClass;
    }

    public function setModel(array|string $model)
    {
        if(is_string($model)){
            $this->hostableClasses = [$model];
        }else{
            $this->hostableClasses = $model;
        }

        $this->setHostModel();

        return $this;
    }

    public function group(callable $callback)
    {
        Route::group($this->getOptions(), function () use($callback){
            $callback();
        });
    }

    public function combineHostModels() : Collection
    {
        return array_reduce($this->hostableClasses, function($carry, $class){
            $carry = $carry->merge($class::hostables());

            return $carry;
        }, Collection::make([]));
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

    public function __call($method, $args)
	{
		if ($method == 'options') {
			return $this->setOptions(...$args);
		}

        if ($method == 'model') {
			return $this->setModel(...$args);
		}

        if (!in_array($method, get_class_methods($this))) {
            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s->%s()', static::class, $method
            ));
        }	}

}
