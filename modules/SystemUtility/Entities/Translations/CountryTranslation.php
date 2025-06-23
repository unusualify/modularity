<?php

namespace Modules\SystemUtility\Entities\Translations;

use Modules\SystemUtility\Entities\Country;
use Unusualify\Modularity\Entities\Model;

class CountryTranslation extends Model
{
    protected $baseModuleModel = Country::class;

    public function getTable(): string
    {
        return modularityConfig('tables.country_translations', 'um_country_translations');
    }
}
