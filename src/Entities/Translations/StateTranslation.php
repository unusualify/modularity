<?php

namespace Unusualify\Modularity\Entities\Translations;

use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Model;

class StateTranslation extends Model
{
    protected $fillable = [
        'name',
        'active',
        'locale',
    ];

    public function getTable()
    {
        return modularityConfig('tables.state_translations', 'modularity_state_translations');
    }
}
