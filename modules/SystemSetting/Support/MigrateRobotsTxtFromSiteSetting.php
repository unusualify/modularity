<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SystemSetting\Entities\General;
use Modules\SystemSetting\Repositories\GeneralRepository;
use Modules\SystemSetting\Services\SystemSettingsService;
use Unusualify\Modularous\Facades\SystemSettings;

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

        if (! database_exists()) {
            return false;
        }

        $legacyTable = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');

        if (! Schema::hasTable($legacyTable)) {
            $this->writeSettledMarker();

            return false;
        }

        if (SystemSettings::has('seo.robots_txt')) {
            $existing = trim((string) SystemSettings::get('seo.robots_txt', ''));

            if ($existing !== '') {
                $this->writeSettledMarker();

                return false;
            }
        }

        [$group, $key, $locale] = $this->legacyRobotsKeys();

        $row = DB::table($legacyTable)
            ->where('group_key', $group)
            ->where('key', $key)
            ->where('locale', $locale)
            ->whereNull('deleted_at')
            ->first();

        if ($row === null || trim((string) ($row->value ?? '')) === '') {
            $this->writeSettledMarker();

            return false;
        }

        $general = General::single();
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
        return storage_path('framework/modularous-robots-txt-migrated');
    }

    protected function hasSettledMarker(): bool
    {
        return is_file(self::settledMarkerPath());
    }

    protected function writeSettledMarker(): void
    {
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
