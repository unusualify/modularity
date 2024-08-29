<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Unusualify\Modularity\Traits\ManageModuleRoute;

trait ModelHelpers
{
    use ManageModuleRoute;

    protected static $definedRelationships = [];

    protected $columnTypes;

    protected $modelCacheKeys = [
        'column_types',
    ];

    public static function bootModelHelpers()
    {
        $relationClassesPattern = app('model.relation.pattern');

        $reflector = new \ReflectionClass(get_called_class());

        $modelName = $reflector->getShortName();

        static::$definedRelationships[$modelName] = collect($reflector->getMethods(\ReflectionMethod::IS_PUBLIC))
            // ->filter(fn(\ReflectionMethod $method) =>  $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            ->reduce(function($carry, \ReflectionMethod $method) use($relationClassesPattern) {
                if($method->hasReturnType() && preg_match("{$relationClassesPattern}", ($returnType = $method->getReturnType()) )){
                    $carry[$method->name] = get_class_short_name((string) $returnType);
                }

                return $carry;
            });
    }

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

    public function getTimestampColumns(): array
    {
        return array_keys(array_filter($this->getColumnTypes(), fn($val) => $val === 'timestamp'));
    }

    public function isTimestampColumn($column): bool
    {
        return in_array($column, $this->getTimestampColumns());
    }

    public function getTitleField()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function getColumnTypes(): array
    {
        $columnsKey = get_class($this) . "_column_types";

        if (Cache::has($columnsKey)) {
            return Cache::get($columnsKey);
        }else{
            $builder = $this->getConnection()->getSchemaBuilder();

            $columnTypes = Arr::mapWithKeys(
                $builder->getColumnListing($this->getTable()),
                fn($column) => [$column => $builder->getColumnType($this->getTable(), $column)]
            );

            Cache::put($columnsKey, $columnTypes);

            return $columnTypes;
        }
    }

    public function getColumns(): array
    {
        return array_keys($this->getColumnTypes());
    }

    public function getShowFormat()
    {
        return $this->{$this->getRouteTitleColumnKey()};
    }

    public function setRelationsShowFormat()
    {
        foreach ($this->getRelations() as $relationName => $relation) {

            if ($relation instanceof \Illuminate\Database\Eloquent\Collection) {
                // dd($this, $relationName, $this->getRelations());
                $this->{$relationName} = $relation->map(function($related) use($relationName){

                    if(method_exists($related, 'setRelationsShowFormat'))
                        $related->setRelationsShowFormat();

                    return $related;
                });

                $this["{$relationName}_show"] ??= $this->{$relationName}->map(fn($model) => modelShowFormat($model))->implode(', ');;

            }else if($relation){

                if(method_exists($relation, 'setRelationsShowFormat'))
                    $relation->setRelationsShowFormat();

                // $this->{$relationName} = $relation;

                $this["{$relationName}_show"] ??= modelShowFormat($relation);

            }
        }
    }


    public function definedRelations($relations = null): array
    {
        $modelName = get_class_short_name(get_called_class());

        $definedRelationships = static::$definedRelationships[$modelName];

        if($relations){
            if(is_array($relations)){
                return array_keys(Arr::where($definedRelationships, fn($val, $key) => in_array($val, $relations)));

            }else if(is_string($relations)){
                return array_keys(Arr::where($definedRelationships, fn($val, $key) => $val == studlyName($relations)));
            }
        }

        return array_keys($definedRelationships);
    }

    public function definedRelationsTypes($relations = null): array
    {
        $modelName = get_class_short_name(get_called_class());

        $definedRelationships = static::$definedRelationships[$modelName];

        if($relations){
            if(is_array($relations)){
                return Arr::where($definedRelationships, fn($val, $key) => in_array($val, $relations));

            }else if(is_string($relations)){
                return Arr::where($definedRelationships, fn($val, $key) => $val == studlyName($relations));
            }
        }

        return $definedRelationships;
    }

    public function hasRelation($relationName): bool
    {
        $modelName = get_class_short_name(get_called_class());

        $definedRelationships = static::$definedRelationships[$modelName];

        return array_key_exists($relationName, $definedRelationships);
    }

    public function getRelationType($relationName): string
    {
        $modelName = get_class_short_name(get_called_class());

        $definedRelationships = static::$definedRelationships[$modelName];

        return array_key_exists($relationName, $definedRelationships) ? $definedRelationships[$relationName] : false;
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
            ->filter(fn(\ReflectionMethod $method) =>  $method->hasReturnType() && preg_match("{$relationClassesPattern}", $method->getReturnType()) )
            ->pluck('name')
            ->all();
    }
}
