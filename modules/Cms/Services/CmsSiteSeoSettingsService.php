<?php

namespace Modules\Cms\Services;

use Modules\Cms\Repositories\SiteSettingRepository;
use Modules\Cms\Support\CmsPublicSeo;

/**
 * Persists site-wide SEO options in {@see \Modules\Cms\Entities\SiteSetting} (key-value rows).
 *
 * Global robots.txt body is stored under the configured group/key/locale and read by
 * {@see \Modules\Cms\Http\Controllers\Front\RobotsTxtController} when `cms_seo.robots.use_site_settings` is true.
 */
class CmsSiteSeoSettingsService
{
    public function __construct(
        protected SiteSettingRepository $siteSettings,
    ) {}

    /**
     * Body served at GET /robots.txt (normalized trailing newline).
     */
    public function resolvedRobotsTxtBody(): string
    {
        if (($staging = CmsPublicSeo::resolvedStagingRobotsTxtBody()) !== null) {
            return $staging;
        }

        $default = "User-agent: *\nAllow: /";
        $raw = null;

        if (modularousConfig('cms_seo.robots.use_site_settings', true)) {
            $persisted = $this->persistedGlobalRobotsTxt();
            if ($persisted !== null) {
                $raw = trim($persisted);
                if ($raw === '') {
                    $raw = null;
                }
            }
        }

        if ($raw === null) {
            $raw = trim((string) modularousConfig('cms_seo.robots.global_robots_txt', $default));
        }

        if ($raw === '') {
            $raw = trim($default);
        }

        return $raw . "\n";
    }

    /**
     * Raw value from DB, or null when unset (use env/config in UI and public fallback).
     */
    public function persistedGlobalRobotsTxt(): ?string
    {
        [$g, $k, $locale] = $this->robotsSettingKeys();
        $row = $this->siteSettings->findScoped($g, $k, $locale);

        return $row !== null ? (string) $row->value : null;
    }

    /**
     * Text shown in the panel editor: DB value if set, otherwise the effective env default (without forcing trailing newline).
     */
    public function globalRobotsTxtForEditor(): string
    {
        if (($staging = CmsPublicSeo::resolvedStagingRobotsTxtBody()) !== null) {
            return rtrim($staging, "\r\n");
        }

        $persisted = $this->persistedGlobalRobotsTxt();
        if ($persisted !== null) {
            return rtrim($persisted, "\r\n");
        }

        $default = "User-agent: *\nAllow: /";
        $raw = trim((string) modularousConfig('cms_seo.robots.global_robots_txt', $default));
        if ($raw === '') {
            $raw = trim($default);
        }

        return $raw;
    }

    public function saveGlobalRobotsTxt(?string $value): void
    {
        [$g, $k, $locale] = $this->robotsSettingKeys();
        $this->siteSettings->putScoped($g, $k, $locale, $value);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    protected function robotsSettingKeys(): array
    {
        $cfg = (array) modularousConfig('cms_seo.robots.site_setting', []);

        return [
            (string) ($cfg['group_key'] ?? 'seo'),
            (string) ($cfg['key'] ?? 'global_robots_txt'),
            (string) ($cfg['locale'] ?? '*'),
        ];
    }
}
