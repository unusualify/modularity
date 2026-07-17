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
        'smtp',
        'analytics',
        'locale',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'site' => 'array',
        'social' => 'array',
        'contact' => 'array',
        'seo' => 'array',
        'smtp' => 'array',
        'analytics' => 'array',
        'locale' => 'array',
        'published' => 'boolean',
    ];

    /**
     * @return list<string>
     */
    public static function settingsSections(): array
    {
        return ['site', 'social', 'contact', 'seo', 'smtp', 'analytics', 'locale'];
    }
}
