<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;

trait PositionTrait
{
    protected bool $hasUserAwareCacheCreatorTrait = true;

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function filterPositionTrait($query, &$scopes)
    {
        $scopes['ordered'] = true;
    }
}
