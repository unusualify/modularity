<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Modularity\Entities\Traits\HasMedias;
use Unusualify\Modularity\Entities\Traits\HasPresenter;
use Unusualify\Modularity\Entities\Traits\HasRelated;

/**
 * No reverse relationship needed.
 * Repeater has one way access from the module it belongs to, (MorphTo).
 * @author Hazarcan Doğa
 * @version ${1:1.0.0}
 * @since 08 Jan 2024
 * @lastModifiedBy Hazarcan Doğa
 */
class Repeater extends Model
{
    public $table = 'umod_repeaters';
    protected $fillable = [
        'repatable_id',
        'content',
        'repeatable_type',
        'role',
        'locale'
    ];
    protected $casts = [
        'content' => 'array',
    ];
}
