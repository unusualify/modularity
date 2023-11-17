<?php

namespace Unusualify\Modularity\Entities\Translations;

use Unusualify\Modularity\Entities\Model;
use Illuminate\Support\Str;

class SettingTranslation extends Model
{
    protected $fillable = [
        'value',
        'active',
        'locale',
    ];

    public function getTable()
    {
        $twillSettingsTable = config(unusualBaseKey() . '.settings_table', 'twill_settings');

        return Str::singular($twillSettingsTable) . '_translations';
    }
}
