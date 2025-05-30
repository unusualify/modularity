<?php

namespace Modules\SystemSetting\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasImages;
use Unusualify\Modularity\Entities\Traits\IsSingular;

class General extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasImages, IsSingular;

    protected $fillable = [
        'name',
        'published',
        'cont',
    ];
}
