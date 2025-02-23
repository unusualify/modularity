<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait CreatorTrait
{
    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterCreatorTrait($query, &$scopes)
    {
        $scopes['authorized'] = true;
    }

    public function afterForceDeleteCreatorTrait($object, $fields)
    {
        $object->creatorModel()->delete();
    }
}
