<?php

namespace Unusualify\Modularity\Tests\Traits\IsTranslatable;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;


class OwnToHasTranslation extends Model
{
    use IsTranslatable, HasTranslation;

    protected $translatedAttributes =
    [
        'title',
        'description',
    ];

}
