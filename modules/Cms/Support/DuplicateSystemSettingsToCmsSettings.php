<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Modules\Cms\Entities\SiteSetting;
use Modules\Cms\Repositories\SiteSettingRepository;
use Modules\Cms\Services\CmsSettingsService;
use Modules\SystemSetting\Entities\General;

/**
 * One-time seed: copy {@see General} singleton content into empty {@see SiteSetting}.
 */
final class DuplicateSystemSettingsToCmsSettings
{
    public function __construct(
        protected SiteSettingRepository $siteSettingRepository,
        protected CmsSettingsService $cmsSettings,
    ) {}

    public function duplicateIfNeeded(): bool
    {
        if (! class_exists(General::class) || ! class_exists(SiteSetting::class)) {
            return false;
        }

        try {
            $general = General::single();
            $siteSetting = SiteSetting::single();
        } catch (\Throwable) {
            return false;
        }

        if ($this->hasMeaningfulContent($siteSetting)) {
            return false;
        }

        if (! $this->hasMeaningfulContent($general)) {
            return false;
        }

        $fields = ['published' => (bool) ($general->published ?? true)];

        foreach (SiteSetting::settingsSections() as $section) {
            $value = $general->getAttribute($section);
            $fields[$section] = is_array($value) ? $value : [];
        }

        $this->siteSettingRepository->update($siteSetting->id, $fields);
        $this->cmsSettings->forgetCache();

        return true;
    }

    protected function hasMeaningfulContent(object $model): bool
    {
        foreach (SiteSetting::settingsSections() as $section) {
            $value = $model->getAttribute($section);

            if (is_array($value) && $value !== []) {
                return true;
            }

            if ($value !== null && $value !== '' && $value !== []) {
                return true;
            }
        }

        return false;
    }
}
