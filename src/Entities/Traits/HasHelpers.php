<?php

namespace Unusualify\Modularity\Entities\Traits;

trait HasHelpers
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
}
