<?php

declare(strict_types=1);

namespace Modules\Cms\Services;

use Modules\Cms\Entities\SiteSetting;
use Modules\Cms\Repositories\SiteSettingRepository;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\Settings\AbstractSingularSettingsService;

/**
 * Read/write frontend CMS settings stored on {@see SiteSetting} (IsSingular).
 */
class CmsSettingsService extends AbstractSingularSettingsService
{
    private const CACHE_KEY = 'cms_settings.snapshot';

    public function __construct(
        protected SiteSettingRepository $siteSettingRepository,
    ) {}

    protected function cacheKey(): string
    {
        return self::CACHE_KEY;
    }

    protected function cacheTtl(): int
    {
        return (int) modularousConfig('cms_settings.cache_ttl', modularousConfig('system_settings.cache_ttl', 3600));
    }

    protected function modelClass(): string
    {
        return SiteSetting::class;
    }

    protected function repository(): Repository
    {
        return $this->siteSettingRepository;
    }

    protected function settingsSections(): array
    {
        return SiteSetting::settingsSections();
    }
}
