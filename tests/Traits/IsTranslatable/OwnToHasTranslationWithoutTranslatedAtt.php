<?php

namespace Unusualify\Modularity\Tests\Traits\IsTranslatable;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;


class OwnToHasTranslationWithoutTranslatedAtt extends Model
{
    use IsTranslatable, HasTranslation;

}
