<?php

namespace Unusual\CRM\Base\Entities;

use Unusual\CRM\Base\Entities\Traits\HasMedias;
use Unusual\CRM\Base\Entities\Traits\HasTranslation;
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
        return "Unusual\CRM\Base\Entities\Translations\SettingTranslation";
    }

    public function getTable()
    {
        return config('twill.settings_table', 'twill_settings');
    }

    protected function getTranslationRelationKey(): string
    {
        return Str::singular(config('twill.settings_table', 'twill_settings')) . '_id';
    }
}
