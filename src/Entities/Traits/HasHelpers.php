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

trait HasHelpers
{
    public function definedRelations($relations = null): array
    {
        $relationNamespace = "Illuminate\Database\Eloquent\Relations";
        $relationClassesPattern = "|" . preg_quote($relationNamespace, "|") . "|";
        if($relations){
            if(is_array($relations)){
                $relationNamespaces = implode('|', Arr::map($relations, function($relationName) use($relationNamespace){
                    return $relationNamespace . "\\{$relationName}";
                }));
                $relationClassesPattern = "|" . preg_quote($relationNamespaces, "|") . "|";

            }else if(is_string($relations)){
                $relationClassesPattern = "|" . preg_quote($relationNamespace . "\{$relations}", "|") . "|";
            }
        }

        // dd($relationClassesPattern);
        // $relationNamespace = "Illuminate\Database\Eloquent\Relations"
        //     . ($relationName ? "\{$relationName}" : null);


        // $model = new static;
        // $relationships = [];
        // foreach((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        // {
        //     if ($method->class != get_class($model) ||
        //         !empty($method->getParameters()) ||
        //         $method->getName() == __FUNCTION__) {
        //         continue;
        //     }
        //     try {
        //         $return = $method->invoke($model);
        //         if ($return instanceof Relation) {
        //             $relationships[$method->getName()] = [
        //                 'type' => (new ReflectionClass($return))->getShortName(),
        //                 'model' => (new ReflectionClass($return->getRelated()))->getName()
        //             ];
        //         }
        //     } catch(ErrorException $e) {
        //         dd(
        //             $model, $method, $e
        //         );
        //     }
        // }
        // return array_keys($relationships);


        // $namespace = 'Illuminate\Database\Eloquent\Relations';
        // $relationClassesPattern = implode('|', ClassFinder::getClassesInNamespace($namespace));
        // $relationClassesPattern = "|" . preg_quote($relationClassesPattern, "|") . "|";

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
