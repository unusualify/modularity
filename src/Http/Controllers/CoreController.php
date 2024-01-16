<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

use Illuminate\Support\Str;

use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Traits\{ManageNames, ManageTraits};

abstract class CoreController extends LaravelController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,
        ManageTraits,
        ManageNames;

    /**
     * baseKey
     *
     * @var string snake_case
     */
    protected $baseKey;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $modelName;


    /**
     * @var \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected $repository;

    public function __construct(Request $request)
    {
        $this->baseKey = unusualBaseKey();

        $this->request = $request;

        $this->moduleName = $this->getModuleName();
        $this->namespace = $this->getNamespace();
        $this->routeName = $this->getRouteName();

        $this->modelName = $this->getModelName();
        $this->repository = $this->getRepository();

    }

    /**
     * @param array $scopes
     * @param bool $forcePagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getIndexItems($with=[], $scopes = [], $forcePagination = false, )
    {
        return $this->transformIndexItems($this->repository->get(
            $this->indexWith + $with,
            $scopes,
            $this->orderScope(),
            $this->request->get('itemsPerPage') ?? $this->perPage ?? 50,
            $forcePagination
        ));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function transformIndexItems($items)
    {
        return $items;
    }

    /**
     *
     *
     * @param  array $paginator
     * @return array
     */
    public function getFormattedIndexItems($paginator) // getIndexTableItems
    {
        return $paginator;
    }

    /**
     * @return string
     */
    protected function getNamespace()
    {
        try {
            return $this->namespace ?? 'Modules'."\\{$this->moduleName}";
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        return $this->moduleName ?? curtModuleName(dirname((new \ReflectionClass(get_class($this)))->getFileName()));
    }

    /**
     * @return string
     */
    protected function getRouteName()
    {
        return $this->routeName ?? $this->routeName() ?? $this->moduleName;
    }

    /**
     * @return string
     */
    protected function getModelName()
    {
        try {
            return $this->modelName ?? ucfirst(Str::singular($this->routeName));
        } catch (\Throwable $th) {
            dd(
                $this
            );
            return $th;
        }
    }

    /**
     * @return \Unusualify\Modularity\Repositories\ModuleRepository
     */
    protected function getRepository()
    {
        try {
            return $this->getRepositoryClass($this->modelName) ? App::make($this->getRepositoryClass($this->modelName)) : null;
            //code...
        } catch (\Throwable $th) {
            dd(
                "repositoryClass not exists for {$this->routeName} in {$this->moduleName}",
            );
            throw $th;
        }
    }

    /**
     * getRepositoryClass
     *
     * @param  mixed $model
     * @return void
     */
    public function getRepositoryClass($model)
    {
        if (@class_exists($class = "$this->namespace\Repositories\\" . $model . 'Repository')) {
            return $class;
        }
        return null;
        // TODO if repository is not exists
        return TwillCapsules::getCapsuleForModel($model)->getRepositoryClass();
    }

    /**
     * @return string
     */
    protected function getModelTitle()
    {
        return $this->getHeadline($this->modelName);
    }

    protected function routeParameters()
    {
        return $this->request->route() ? $this->request->route()->parameters() : [];
    }

    protected function routeModuleParameters(){
        return Arr::mapWithKeys($this->routeParameters(), function($value, $snakeName){
            return [ $this->getStudlyName($snakeName) => $value];
        });
    }

    protected function routeParameter(){
        $filtered =  Arr::where($this->routeParameters(), function ($value, $snakeName) {
            return $this->getStudlyName($snakeName) == $this->routeName;
        });

        return $filtered[$this->getSnakeCase($this->routeName)] ?? null;
    }

    protected function routeParentModuleParameters(){
        $filtered =  Arr::where($this->routeParameters(), function ($value, $snakeName) {
            return $this->getStudlyName($snakeName) !== $this->routeName;
        });

        return Arr::mapWithKeys($filtered, function($value, $snakeName){
            return [ $this->getStudlyName($snakeName) => $value];
        });
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHasTrait($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    /**
     * @param string $behavior
     * @return bool
     */
    protected function routeHas($behavior)
    {
        return $this->repository->hasBehavior($behavior);
    }

    /**
     * @return void
     */
    public function __afterConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }

    /**
     * @return void
     */
    public function __beforeConstruct(...$args)
    {
        foreach ($this->traitsMethods(__FUNCTION__) as $method) {
            $this->$method(...$args);
        }
    }
}
