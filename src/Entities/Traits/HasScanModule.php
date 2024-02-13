<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Unusualify\Modularity\Facades\Modularity;
use ReflectionMethod;

trait HasScanModule
{
    protected $isModuleModel = false;

    protected $moduleName;

    protected $routeName;

    protected $moduleRelationships = [];

    protected $definedRelationships = [];


    public function initializeHasScanModule()
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

            $this->moduleRelationships = Modularity::find($this->moduleName)->getRouteConfigs(snakeCase($this->routeName) . '.relationships', []);

            $this->definedRelationships = Arr::map($this->moduleRelationships, fn($r) => get_class_short_name($r['return_type'])) + $this->getOriginalRelations();

        }
    }

    public function __call($method, $arguments)
    {
        $moduleRelationships = $this->moduleRelationships ?? [];

        if (array_key_exists($method, $moduleRelationships)) {
            $relationship = $moduleRelationships[$method];
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

    public function _definedRelations($relations = null): array
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

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn($method) => preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            // ->filter(function($method) use($relationClassesPattern){
            //     return !empty($returnType = $method->getReturnType())
            //         ? preg_match("{$relationClassesPattern}", $returnType)
            //         : tryOperation(fn() => $this->{$method->name}()) instanceof Relation;
            // })
            ->pluck('name')
            ->all();
    }

    public function getOriginalRelations(): array
    {
        $relationClassesPattern = app('model.relation.pattern');

        $reflector = new \ReflectionClass(get_called_class());

        $builtInMethods = app('model.builtin.methods');

        if(get_class_short_name($this) == 'WebCompanyx')
            dd($reflector);

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function($carry, $method) use($relationClassesPattern, $builtInMethods){

            if(!in_array($method->name, $builtInMethods) && $method->getNumberOfParameters() < 1){
                if($method->hasReturnType()){
                    if(preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))){
                        $carry[$method->name] = get_class_short_name((string) $returnType);
                    }
                }else {
                    // dd($this, $method);
                    try {
                        // $return = $this->{$method->name}();
                        // dd($this, $method, $method->invoke($this));
                        $return = $method->invoke($this);

                        if( $return instanceof Relation){
                            $carry[$method->name] = get_class_short_name($return);
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }

            return $carry;
        }, []);

        dd(
            // collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->filter(fn($method) => $method->getNumberOfParameters() < 1)->mapWithKeys(fn($v, $k) => [$v->name => $v->getNumberOfParameters()]),
        );

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            // ->filter(fn($method) => preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            ->filter(function($method) use($relationClassesPattern){
                return !empty($returnType = $method->getReturnType())
                    ? preg_match("{$relationClassesPattern}", $returnType)
                    : tryOperation(fn() => $this->{$method->name}()) instanceof Relation;
            })
            ->pluck('name')
            ->all();
    }

    public function getDefinedRelations($relations = null) {
        $relationships = $this->definedRelationships;

        if($relations){
            $relationships = Arr::where($relationships, function($relationType) use($relations){
                return is_array($relations) ? in_array($relationType, $relations): $relations == $relationType;
            });
        }

        return array_keys($relationships);
    }

    public function getDefinedRelationTypes() {
        return $this->definedRelationships;
    }
}
