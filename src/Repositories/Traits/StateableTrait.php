<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait StateableTrait
{
    public function getStateableFilterList()
    {
        $scopes = [];

        $query = $this->getModel()->newQuery();

        foreach ($this->traitsMethods('filter') as $method) {
            $this->$method($query, $scopes);
        }

        return $this->getModel()->defaultStateables($scopes);
    }
}
