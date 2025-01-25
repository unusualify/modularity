<?php

namespace Unusualify\Modularity\Entities\Translations;

use Illuminate\Support\Str;
use Unusualify\Modularity\Entities\Model;

class SettingTranslation extends Model
{
    protected $fillable = [
        'value',
        'active',
        'locale',
    ];

    public function getTable()
    {
        $twillSettingsTable = modularityConfig('settings_table', 'twill_settings');

        return Str::singular($twillSettingsTable) . '_translations';
    }
}
