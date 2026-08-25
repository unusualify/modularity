<?php

namespace Modules\Cms\Entities;

use Modules\Cms\Entities\Concerns\IsCmr;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\IsSingular;
use Unusualify\Modularous\Entities\Traits\Publishable;

class HomepageTest extends Model
{
    use HasFileponds,
        HasImages,
        IsSingular,
        IsCmr,
        HasTranslatableMetadata,
        HasRevisions,
        Publishable;

    public bool $usePublishDates = true;

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
