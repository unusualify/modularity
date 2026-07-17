<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Cms\Services\CmsSettingsService;

/**
 * @method static mixed get(string $key, mixed $default = null, ?string $locale = null)
 * @method static mixed value(string $key, mixed $default = null, ?string $locale = null)
 * @method static mixed first(string $key, mixed $default = null, ?string $locale = null)
 * @method static array all(?string $locale = null)
 * @method static bool has(string $key)
 * @method static bool filled(string $key, ?string $locale = null)
 * @method static void set(string $key, mixed $value)
 * @method static void forgetCache()
 * @method static array snapshot()
 *
 * @see CmsSettingsService
 */
class CmsSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cms.settings';
    }
}
