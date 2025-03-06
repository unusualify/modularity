<?php

namespace Unusualify\Modularity\Tests\Traits\HasTranslation\Models;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Unusualify\Modularity\Tests\Traits\HasTranslation\Models\Translations\PackageTranslation;


class Package extends Model
{
    use HasTranslation;

    static $hasTranslationModel = PackageTranslation::class;


}
