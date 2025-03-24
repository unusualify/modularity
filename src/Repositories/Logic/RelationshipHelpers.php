<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Illuminate\Support\Arr;

trait RelationshipHelpers
{
    /**
     * @param array|string|null $relations
     * @return array
     */
    public function definedRelations($relations = null): array
    {
        if (method_exists($this->model, 'definedRelations')) {
            return $this->model->definedRelations($relations);
        }

        $relationNamespace = "Illuminate\Database\Eloquent\Relations";

        $relationClassesPattern = '|' . preg_quote($relationNamespace, '|') . '|';

        if ($relations) {
            if (is_array($relations)) {
                $relationNamespaces = implode('|', Arr::map($relations, function ($relationName) use ($relationNamespace) {
                    return $relationNamespace . '\\' . $relationName;
                }));
                $relationClassesPattern = '|' . preg_quote($relationNamespaces, '|') . '|';

            } elseif (is_string($relations)) {
                $relationClassesPattern = '|' . preg_quote($relationNamespace . '\\' . $relations, '|') . '|';
            }
        }

        $reflector = new \ReflectionClass($this->getModel());

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))->reduce(function ($carry, $method) use ($relationClassesPattern) {

            if ($method->getNumberOfParameters() < 1) {
                if ($method->hasReturnType()) {
                    if (preg_match($relationClassesPattern, ($returnType = $method->getReturnType()))) {
                        $carry[] = $method->name;
                    }
                } else {

                }
            }

            return $carry;
        }, []);

        return collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->filter(fn (\ReflectionMethod $method) => $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()))
            ->pluck('name')
            ->all();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation $related
     * @return string
     */
    private function getForeignKeyBelongsToMany($related)
    {
        if (method_exists($related, 'getRelatedPivotKeyName')) {
            $foreignKey = $related->getRelatedPivotKeyName();
        }

        return $foreignKey;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation $related
     * @return string
     */
    private function getForeignKeyBelongsTo($related)
    {
        $foreignKey = $related->getForeignKeyName();

        return $foreignKey;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation $related
     * @return string
     */
    private function getForeignKeyHasManyThrough($related)
    {
        $foreignKey = $related->getSecondLocalKeyName();

        return $foreignKey;
    }
}