<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasRepeaters;
use Unusualify\Modularous\Entities\Traits\IsSingular;

class General extends Model
{
    use HasImages, IsSingular, HasRepeaters;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'published',
        'site',
        'social',
        'contact',
        'seo',
        'scripts',
        'smtp',
        'analytics',
        'locale',
        'down_presets',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'site' => 'array',
        'social' => 'array',
        'contact' => 'array',
        'seo' => 'array',
        'scripts' => 'array',
        'smtp' => 'array',
        'analytics' => 'array',
        'locale' => 'array',
        'down_presets' => 'array',
        'published' => 'boolean',
    ];

    /**
     * @return list<string>
     */
    public static function settingsSections(): array
    {
        return ['site', 'social', 'contact', 'seo', 'scripts', 'smtp', 'analytics', 'locale', 'down_presets'];
    }
}
