<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use ErrorException;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionMethod;
use Unusualify\Modularity\Facades\Modularity;

trait HasHelpers
{
    protected $isModuleModel = false;

    protected $moduleName;

    protected $routeName;

    protected $definedRelationships = [];


    public function initializeHasHelpers()
    {
        if(!app()->runningInConsole())
            $this->scanModuleModel();
    }

    public function scanModuleModel()
    {
        $pattern = "/" . preg_quote( trim(config('modules.namespace', 'Modules'), '\\') . "\\", "|") . "/";

        if($this->isModuleModel = preg_match($pattern, static::class, $matches)){
            $moduleNamePattern = '/Modules\\\([A-Za-z]+)+\\\Entities/';

            preg_match($moduleNamePattern, static::class, $matches);

            $this->moduleName = $matches[1];

            $this->routeName = get_class_short_name(static::class);

            $this->definedRelationships = Modularity::find($this->moduleName)->getRouteConfigs(snakeCase($this->routeName) . '.relationships', []);
            // $this->definedRelationships = app()['unusual.modularity']->find($this->moduleName)->getRouteConfigs(snakeCase($this->routeName) . '.relationships', []);
        }
    }

    public function __call($method, $arguments)
    {
        $definedRelationships = $this->definedRelationships ?? [];

        if (array_key_exists($method, $definedRelationships)) {

            $relationship = $definedRelationships[$method];
            $methods = $this->{$relationship['method']}(...$relationship['parameters']);

            if(isset($relationship['chain_methods']) && count($relationship['chain_methods']) > 0){
                foreach ($relationship['chain_methods'] as $chain_method) {
                    isset($chain_method['method_name'])
                        ? $methods = $methods->{$chain_method['method_name']}(...($chain_method['parameters'] ?? []))
                        : null;
                }
            }

            return $methods;
        }

        return parent::__call($method, $arguments);
    }

    public function definedRelations($relations = null): array
    {
        $relationNamespace = "Illuminate\Database\Eloquent\Relations";

        $relationClassesPattern = "|" . preg_quote($relationNamespace, "|") . "|";

        if($relations){
            if(is_array($relations)){
                $relationNamespaces = implode('|', Arr::map($relations, function($relationName) use($relationNamespace){
                    return $relationNamespace . "\\" . $relationName;
                }));
                $relationClassesPattern = "|" . preg_quote($relationNamespaces, "|") . "|";

            }else if(is_string($relations)){
                $relationClassesPattern = "|" . preg_quote($relationNamespace . "\\" . $relations, "|") . "|";
            }
        }

        $reflector = new \ReflectionClass(get_called_class());

        return collect($reflector->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(
                fn($method) => !empty($method->getReturnType()) &&
                    preg_match("{$relationClassesPattern}", $method->getReturnType())
                    // str_contains(
                    //     $method->getReturnType(),
                    //     $relationNamespace
                    // )
            )
            ->pluck('name')
            ->all();
    }

    /**
     * Checks if this model is soft deletable.
     *
     * @param array|string|null $columns Optionally limit the check to a set of columns.
     * @return bool
     */
    public function isSoftDeletable()
    {
        // Model must have the trait
        if (!classHasTrait($this, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return false;
        }

        return true;
    }

    public function hasColumn($column){
        return $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $column);
    }

}
