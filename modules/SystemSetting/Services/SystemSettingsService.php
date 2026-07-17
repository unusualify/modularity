<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Services;

use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Repositories\GeneralRepository;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\Settings\AbstractSingularSettingsService;

/**
 * Read/write global system settings stored on {@see General} (IsSingular).
 */
class SystemSettingsService extends AbstractSingularSettingsService
{
    private const CACHE_KEY = 'system_settings.snapshot';

    public function __construct(
        protected GeneralRepository $generalRepository,
    ) {}

    protected function cacheKey(): string
    {
        return self::CACHE_KEY;
    }

    protected function cacheTtl(): int
    {
        return (int) modularousConfig('system_settings.cache_ttl', 3600);
    }

    protected function modelClass(): string
    {
        return General::class;
    }

    protected function repository(): Repository
    {
        return $this->generalRepository;
    }

    protected function settingsSections(): array
    {
        return General::settingsSections();
    }
}
