<?php

namespace Unusualify\Modularity\Entities\Traits;

trait HasScopes
{
    public function scopePublished($query)
    {
        return $query->where("{$this->getTable()}.published", true);
    }

    public function scopePublishedInListings($query)
    {
        if ($this->isFillable('public')) {
            $query->where("{$this->getTable()}.public", true);

        }

        return $query->published()->visible();
    }

    public function scopeVisible($query)
    {
        if ($this->isFillable('publish_start_date')) {
            $query->where(function ($query) {
                $query->whereNull("{$this->getTable()}.publish_start_date")->orWhere("{$this->getTable()}.publish_start_date", '<=', Carbon::now());
            });

            if ($this->isFillable('publish_end_date')) {
                $query->where(function ($query) {
                    $query->whereNull("{$this->getTable()}.publish_end_date")->orWhere("{$this->getTable()}.publish_end_date", '>=', Carbon::now());
                });
            }
        }

        return $query;
    }

    public function scopeDraft($query)
    {
        return $query->where("{$this->getTable()}.published", false);
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->whereNotNull("{$this->getTable()}.deleted_at");
    }
}
