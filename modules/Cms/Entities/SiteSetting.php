<?php

declare(strict_types=1);

namespace Modules\Cms\Entities;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasRepeaters;
use Unusualify\Modularous\Entities\Traits\IsSingular;

/**
 * Frontend/CMS-facing site settings singleton (overrides subset of {@see \Modules\SystemSetting\Entities\General}).
 *
 * Stored on um_singletons via {@see IsSingular}. Legacy KV rows remain on um_cms_site_settings
 * for one-time migrations only — they are not read by this model.
 */
class SiteSetting extends Model
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
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'site' => 'array',
        'social' => 'array',
        'contact' => 'array',
        'seo' => 'array',
        'published' => 'boolean',
    ];

    /**
     * @return list<string>
     */
    public static function settingsSections(): array
    {
        return ['site', 'social', 'contact', 'seo'];
    }
}
