<?php

namespace Modules\SystemUser\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\SystemUser\Entities\Traits\FlushesSecurityCache;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\Publishable;

class Capability extends Model
{
    use FlushesSecurityCache, Publishable;

    protected $fillable = [
        'name',
        'title',
        'strict_route_binding',
        'requires_step_up'
    ];

    // protected $casts = [
    //     'strict_route_binding' => 'boolean',
    //     'requires_step_up' => 'boolean',
    //     'published' => 'boolean',
    // ];

    public function getTable()
    {
        return modularousConfig('tables.capabilities', parent::getTable());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            modularousConfig('tables.role_capability', 'um_role_capability'),
            'capability_id',
            'role_id'
        );
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(
            CapabilityRoute::class,
            modularousConfig('tables.capability_capability_route', 'um_capability_capability_route'),
            'capability_id',
            'capability_route_id'
        );
    }
}
