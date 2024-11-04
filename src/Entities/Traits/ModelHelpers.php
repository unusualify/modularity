<?php

namespace Unusualify\Modularity\Entities\Traits;

use Oobook\Database\Eloquent\Concerns\ManageEloquent;
use Unusualify\Modularity\Traits\ManageModuleRoute;

trait ModelHelpers
{
    use ManageEloquent, ManageModuleRoute, HasScopes;

    /**
     * Checks if this model is soft deletable.
     *
     * @param array|string|null $columns Optionally limit the check to a set of columns.
     */
    public function isSoftDeletable(): bool
    {
        // Model must have the trait
        if (! classHasTrait($this, 'Illuminate\Database\Eloquent\SoftDeletes')) {
            return false;
        }

        return true;
    }

    public function getTitleField()
    {
        return $this->{$this->getRouteTitleColumnKey()};
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
                $this->{$relationName} = $relation->map(function ($related) {

                    if (method_exists($related, 'setRelationsShowFormat')) {
                        $related->setRelationsShowFormat();
                    }

                    return $related;
                });

                $this["{$relationName}_show"] ??= $this->{$relationName}->map(fn ($model) => modelShowFormat($model))->implode(', ');

            } elseif ($relation) {

                if (method_exists($relation, 'setRelationsShowFormat')) {
                    $relation->setRelationsShowFormat();
                }

                // $this->{$relationName} = $relation;

                $this["{$relationName}_show"] ??= modelShowFormat($relation);

            }
        }
    }
}
