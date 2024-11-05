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
        $stateSettingsTable = unusualConfig('settings_table', 'state');

        return Str::singular($stateSettingsTable) . '_translations';
    }
}
