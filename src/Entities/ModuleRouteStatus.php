<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Entities;

use Illuminate\Database\Eloquent\Model;

class ModuleRouteStatus extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'module',
        'route',
        'enabled',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function getTable(): string
    {
        return modularousConfig('tables.module_route_statuses', 'um_module_route_statuses');
    }
}
