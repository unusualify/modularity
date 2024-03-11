<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait AuthorizedTrait
{
    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterAuthorizedTrait($query, &$scopes)
    {
        $scopes['authorized'] = true;
    }

    public function afterForceDeleteAuthorizedTrait($object, $fields)
    {
        $object->authorized()->delete();
    }
}
