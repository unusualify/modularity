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
        if ($this->hasSettledMarker()) {
            return false;
        }

        if (! class_exists(General::class) || ! class_exists(SiteSetting::class)) {
            return false;
        }

        if (! database_exists()) {
            return false;
        }

        try {
            $general = General::single();
            $siteSetting = SiteSetting::single();
        } catch (\Throwable) {
            return false;
        }

        if ($this->hasMeaningfulContent($siteSetting)) {
            $this->writeSettledMarker();

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
        $this->writeSettledMarker();

        return true;
    }

    public static function settledMarkerPath(): string
    {
        // Under storage/framework/cache — ignored by storage/framework/cache/.gitignore
        return storage_path('framework/cache/modularous-cms-settings-duplicated');
    }

    /**
     * Legacy path before markers lived under the ignored cache/ directory.
     */
    public static function legacySettledMarkerPath(): string
    {
        return storage_path('framework/modularous-cms-settings-duplicated');
    }

    protected function hasSettledMarker(): bool
    {
        if (! $this->markersEnabled()) {
            return false;
        }

        if (is_file(self::settledMarkerPath())) {
            return true;
        }

        $legacy = self::legacySettledMarkerPath();
        if (! is_file($legacy)) {
            return false;
        }

        $this->writeSettledMarker();
        @unlink($legacy);

        return true;
    }

    protected function writeSettledMarker(): void
    {
        if (! $this->markersEnabled()) {
            return;
        }

        $path = self::settledMarkerPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        @touch($path);
    }

    protected function markersEnabled(): bool
    {
        // ParaTest workers share the host filesystem; a marker written by one
        // process would short-circuit duplicateIfNeeded() in another.
        if (defined('MODULAROUS_TEST_TOKEN') || getenv('TEST_TOKEN') !== false) {
            return false;
        }

        return ! app()->runningUnitTests();
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
