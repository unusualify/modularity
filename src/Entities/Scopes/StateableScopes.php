<?php

namespace Unusualify\Modularity\Entities\Scopes;

trait StateableScopes
{
    public function scopeIsStateables($query, $codes)
    {
        return $query->whereHas('state', function ($q) use ($codes) {
            $q->whereIn($q->getModel()->getTable() . '.code', $codes);
        });
    }

    public function scopeIsStateablesCount($query, $codes)
    {
        return $query->isStateables($codes)->count();
    }

    public function scopeIsStateable($query, $code)
    {
        return $query->whereHas('state', function ($q) use ($code) {
            $q->where($q->getModel()->getTable() . '.code', $code);
        });
    }

    public function scopeIsStateableCount($query, $code)
    {
        return $query->isStateable($code)->count();
    }
}
