<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Cms\Services\CmsSettingsService;
use Modules\Cms\Services\SiteSettingsService;
use Modules\SystemSetting\Services\SystemSettingsService;

/**
 * Context-aware settings: frontend → CmsSettings (fallback SystemSettings), backend → SystemSettings.
 *
 * @method static mixed whileFrontend(callable $callback)
 * @method static SiteSettingsService forFrontend()
 * @method static SiteSettingsService forBackend()
 * @method static SiteSettingsService forCms()
 * @method static SiteSettingsService forSystem()
 * @method static bool usesCmsLayer()
 * @method static mixed get(string $key, mixed $default = null, ?string $locale = null)
 * @method static mixed value(string $key, mixed $default = null, ?string $locale = null)
 * @method static mixed first(string $key, mixed $default = null, ?string $locale = null)
 * @method static array all(?string $locale = null)
 * @method static bool has(string $key)
 * @method static bool filled(string $key, ?string $locale = null)
 * @method static void set(string $key, mixed $value)
 * @method static void forgetCache()
 * @method static array snapshot()
 * @method static CmsSettingsService cms()
 * @method static SystemSettingsService system()
 *
 * @see SiteSettingsService
 */
class SiteSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'site.settings';
    }
}
