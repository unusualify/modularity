<?php

namespace Unusual\CRM\Base\Entities\Translations;

use Unusual\CRM\Base\Entities\Model;
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
        $twillSettingsTable = config('twill.settings_table', 'twill_settings');

        return Str::singular($twillSettingsTable) . '_translations';
    }
}
