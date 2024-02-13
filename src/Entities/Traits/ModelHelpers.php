<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Arr;

trait ModelHelpers
{

    /**
     * Checks if this model is soft deletable.
     *
     * @param array|string|null $columns Optionally limit the check to a set of columns.
     * @return bool
     */
    public function isSoftDeletable(): bool
    {
        // Model must have the trait
        if (!classHasTrait($this, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return false;
        }

        return true;
    }

    public function hasColumn($column): bool
    {
        return $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $column);
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

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn(\ReflectionMethod $method) =>  $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            ->pluck('name')
            ->all();
    }
}
