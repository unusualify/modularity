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
        $legacyTable = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');

        if (! Schema::hasTable($legacyTable)) {
            return false;
        }

        if (SystemSettings::has('seo.robots_txt')) {
            $existing = trim((string) SystemSettings::get('seo.robots_txt', ''));

            if ($existing !== '') {
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
            return false;
        }

        $general = General::single();
        $seo = is_array($general->seo) ? $general->seo : [];
        $seo['robots_txt'] = rtrim((string) $row->value, "\r\n");

        $this->generalRepository->update($general->id, [
            'seo' => $seo,
        ]);

        $this->systemSettings->forgetCache();

        return true;
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
