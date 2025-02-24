<?php

namespace Modules\SystemNotification\Entities;

use Unusualify\Modularity\Entities\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'published',
    ];
}
