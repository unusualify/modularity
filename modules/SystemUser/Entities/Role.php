<?php

namespace Modules\SystemUser\Entities;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function scopeClient($query)
    {
        return $query->where('name', 'like', '%client%');
    }
}