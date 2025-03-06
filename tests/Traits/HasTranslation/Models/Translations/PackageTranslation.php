<?php

namespace Unusualify\Modularity\Tests\Traits\HasTranslation\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Tests\Traits\HasTranslation\Models\Package;

class PackageTranslation extends Model
{
    protected $baseModuleModel = Package::class;
}
