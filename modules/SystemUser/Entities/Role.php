<?php

namespace Modules\SystemUser\Entities;

use Spatie\Permission\Models\Role as SpatieRole;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class Role extends SpatieRole
{
    use ModelHelpers;

    public function scopeClient($query)
    {
        return $query->where('name', 'like', '%client%');
    }
}
