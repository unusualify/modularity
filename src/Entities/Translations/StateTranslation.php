<?php

namespace Unusualify\Modularity\Entities\Translations;

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
        return modularityConfig('tables.state_translations', 'um_state_translations');
    }
}
