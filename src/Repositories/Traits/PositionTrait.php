<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularous\Models\Model;

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
