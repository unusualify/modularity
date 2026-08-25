<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Repositories\GeneralRepository;
use Modules\SystemSetting\Services\SystemSettingsService;

/**
 * One-time migration: legacy KV um_cms_site_settings robots.txt row → {@see General} singleton.
 */
class MigrateRobotsTxtFromSiteSetting
{
    public function __construct(
        protected GeneralRepository $generalRepository,
        protected SystemSettingsService $systemSettings,
    ) {}

    public function migrateIfNeeded(): bool
    {
        if ($this->hasSettledMarker()) {
            return false;
        }

        $legacyTable = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');

        try {
            if (! Schema::hasTable($legacyTable)) {
                // Do not mark settled: the legacy table may appear after migrations.
                return false;
            }
        } catch (\Throwable) {
            return false;
        }

        // Read the singleton row directly — SystemSettings cache can be polluted
        // across PHPUnit tests in the same ParaTest worker process.
        try {
            $general = General::query()->first();
        } catch (\Throwable) {
            return false;
        }

        if ($general !== null) {
            $existingSeo = is_array($general->seo) ? $general->seo : [];
            if (trim((string) ($existingSeo['robots_txt'] ?? '')) !== '') {
                $this->writeSettledMarker();

                return false;
            }
        }

        [$group, $key, $locale] = $this->legacyRobotsKeys();

        try {
            $rowQuery = DB::table($legacyTable)
                ->where('group_key', $group)
                ->where('key', $key)
                ->where('locale', $locale);

            if (Schema::hasColumn($legacyTable, 'deleted_at')) {
                $rowQuery->whereNull('deleted_at');
            }

            $row = $rowQuery->first();
        } catch (\Throwable) {
            return false;
        }

        if ($row === null || trim((string) ($row->value ?? '')) === '') {
            $this->writeSettledMarker();

            return false;
        }

        $general ??= General::single();
        $seo = is_array($general->seo) ? $general->seo : [];
        $seo['robots_txt'] = rtrim((string) $row->value, "\r\n");

        $this->generalRepository->update($general->id, [
            'seo' => $seo,
        ]);

        $this->systemSettings->forgetCache();
        $this->writeSettledMarker();

        return true;
    }

    public static function settledMarkerPath(): string
    {
        // Under storage/framework/cache — ignored by storage/framework/cache/.gitignore
        return storage_path('framework/cache/modularous-robots-txt-migrated');
    }

    /**
     * Legacy path before markers lived under the ignored cache/ directory.
     */
    public static function legacySettledMarkerPath(): string
    {
        return storage_path('framework/modularous-robots-txt-migrated');
    }

    protected function markersEnabled(): bool
    {
        // ParaTest workers share the host filesystem; a marker written by one
        // process would short-circuit migrateIfNeeded() in another.
        if (defined('MODULAROUS_TEST_TOKEN') || getenv('TEST_TOKEN') !== false) {
            return false;
        }

        return ! app()->runningUnitTests();
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

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function legacyRobotsKeys(): array
    {
        $cfg = (array) modularousConfig('cms_seo.robots.legacy_site_setting', []);

        return [
            (string) ($cfg['group_key'] ?? 'seo'),
            (string) ($cfg['key'] ?? 'global_robots_txt'),
            (string) ($cfg['locale'] ?? '*'),
        ];
    }
}
