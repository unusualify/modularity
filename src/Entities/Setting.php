<?php

namespace Unusualify\Modularity\Entities;

use Unusualify\Modularity\Entities\Traits\HasMedias;
use Unusualify\Modularity\Entities\Traits\HasTranslation;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasTranslation, HasMedias;

    public $useTranslationFallback = true;

    protected $fillable = [
        'key',
        'section',
    ];

    public $translatedAttributes = [
        'value',
        'locale',
        'active',
    ];

    public function getTranslationModelNameDefault()
    {
        return "Unusualify\Modularity\Entities\Translations\SettingTranslation";
    }

    public function getTable()
    {
        return config(unusualBaseKey() . '.settings_table', 'twill_settings');
    }

    protected function getTranslationRelationKey(): string
    {
        return Str::singular(config(unusualBaseKey() . '.settings_table', 'twill_settings')) . '_id';
    }
}
