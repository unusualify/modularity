<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait AuthorizableTrait
{
    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $scopes
     * @return void
     */
    public function filterAuthorizableTrait($query, &$scopes)
    {
        // set, cast, unset or manipulate the scopes by using query and scopes
        $scopes['hasAuthorization'] = true;
    }
}
