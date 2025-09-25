<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\SystemPricing\Entities\Price;

class PaymentableRelation extends MorphTo
{
    protected Price $priceModel;

    public function __construct(Model $parent)
    {
        $this->priceModel = new Price();

        // Set up the relation as paymentable_type/paymentable_id from the global scope subselects
        $relation = 'priceable';
        $morphType = 'priceable_type';
        $foreignKey = 'priceable_id';
        $ownerKey = 'id';

        parent::__construct(
            $parent->price->newQuery()->setEagerLoads([]),
            $parent->price,
            $foreignKey,
            $ownerKey,
            $morphType,
            $relation
        );
    }

    public function addConstraints()
    {
        if (static::$constraints) {
            // For lazy loading, we need the type and id from the parent model
            dd($this->parent);
            $type = $this->parent->getAttribute($this->morphType);
            $id = $this->parent->getAttribute($this->foreignKey);

            if ($type && $id) {
                // Create the related model instance
                $relatedModel = $this->createModelByType($type);

                // Set up query with price join
                $this->query = $relatedModel->newQuery()
                    ->select($relatedModel->getTable() . '.*')
                    ->join($this->priceModel->getTable(), function ($join) use ($type, $relatedModel) {
                        $join->on($this->priceModel->getTable() . '.priceable_id', '=', $relatedModel->getTable() . '.id')
                            ->where($this->priceModel->getTable() . '.priceable_type', '=', $type);
                    })
                    ->where($relatedModel->getTable() . '.' . $relatedModel->getKeyName(), '=', $id);
            }
        }
    }

    public function addEagerConstraints(array $models)
    {
        // Build dictionary of models by type and id
        $this->buildDictionary($this->models = new EloquentCollection($models));
    }

    protected function buildDictionary(EloquentCollection $models)
    {
        foreach ($models as $model) {
            $type = $model->getAttribute($this->morphType);
            $id = $model->getAttribute($this->foreignKey);

            if ($type && $id) {
                $this->dictionary[$type][$id][] = $model;
            }
        }
    }

    public function getEager()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }

        return $this->models;
    }

    protected function getResultsByType($type)
    {
        $relatedModel = $this->createModelByType($type);
        $ownerKey = $relatedModel->getKeyName();

        // Get the IDs for this type
        $ids = array_keys($this->dictionary[$type]);

        // Query with price join and whereIn for the priceable_ids
        return $relatedModel->newQuery()
            ->select($relatedModel->getTable() . '.*')
            ->join($this->priceModel->getTable(), function ($join) use ($type, $relatedModel) {
                $join->on($this->priceModel->getTable() . '.priceable_id', '=', $relatedModel->getTable() . '.id')
                    ->where($this->priceModel->getTable() . '.priceable_type', '=', $type);
            })
            ->whereIn($relatedModel->getTable() . '.' . $ownerKey, $ids)
            ->get();
    }

    protected function matchToMorphParents($type, EloquentCollection $results)
    {
        foreach ($results as $result) {
            $ownerKey = $result->getKey();

            if (isset($this->dictionary[$type][$ownerKey])) {
                foreach ($this->dictionary[$type][$ownerKey] as $model) {
                    $model->setRelation($this->relationName, $result);
                }
            }
        }
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        // For existence queries (whereHas), we need to join through prices
        $parentTable = $this->parent->getTable();
        $pricesTable = $this->priceModel->getTable();
        $relatedTable = $query->getModel()->getTable();
        $relatedClass = get_class($query->getModel());

        return $query->select($columns)
            ->join($pricesTable, function ($join) use ($parentTable, $pricesTable, $relatedTable, $relatedClass) {
                $join->on($pricesTable . '.priceable_id', '=', $relatedTable . '.id')
                    ->whereColumn($parentTable . '.price_id', $pricesTable . '.id')
                    ->where($pricesTable . '.priceable_type', '=', $relatedClass);
            });
    }

    public function getResults()
    {
        $type = $this->parent->getAttribute($this->morphType);
        $id = $this->parent->getAttribute($this->foreignKey);

        if (!$type || !$id) {
            return null;
        }

        $relatedModel = $this->createModelByType($type);

        return $relatedModel->newQuery()
            ->select($relatedModel->getTable() . '.*')
            ->join($this->priceModel->getTable(), function ($join) use ($type, $relatedModel) {
                $join->on($this->priceModel->getTable() . '.priceable_id', '=', $relatedModel->getTable() . '.id')
                    ->where($this->priceModel->getTable() . '.priceable_type', '=', $type);
            })
            ->where($relatedModel->getTable() . '.' . $relatedModel->getKeyName(), $id)
            ->first();
    }
}
