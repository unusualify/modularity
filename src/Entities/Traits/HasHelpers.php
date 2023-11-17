<?php

namespace Unusualify\Modularity\Entities\Traits;

use Unusualify\Modularity\Entities\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use ErrorException;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;

trait HasHelpers
{
    public function definedRelations(): array
    {
        $model = new static;

        $relationships = [];

        foreach((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if ($method->class != get_class($model) ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__) {
                continue;
            }

            try {
                $return = $method->invoke($model);

                if ($return instanceof Relation) {
                    $relationships[$method->getName()] = [
                        'type' => (new ReflectionClass($return))->getShortName(),
                        'model' => (new ReflectionClass($return->getRelated()))->getName()
                    ];
                }
            } catch(ErrorException $e) {}
        }

        return array_keys($relationships);

        $reflector = new \ReflectionClass(get_called_class());

        dd(
            $this->getRelations()
        );
        foreach ($reflector->getMethods() as $method) {
           dd($method->getReturnType());
        }
        return collect($reflector->getMethods())
            ->filter(
                fn($method) => !empty($method->getReturnType()) &&
                    str_contains(
                        $method->getReturnType(),
                        'Illuminate\Database\Eloquent\Relations'
                    )
            )
            ->pluck('name')
            ->all();
    }

}
