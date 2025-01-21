<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Modules\SystemUtility\Entities\State;

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

