<?php

namespace Modules\Cms\Services;

use Modules\Cms\Support\CmsPublicSeo;
use Unusualify\Modularous\Facades\SiteSettings;

/**
 * Site-wide SEO helpers. Global robots.txt and llms.txt are resolved via {@see SiteSettings}
 * (CmsSettings on frontend with SystemSettings fallback).
 */
class CmsSiteSeoSettingsService
{
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

        if (modularousConfig('cms_seo.robots.use_system_settings', true)) {
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
     * Body served at GET /llms.txt (normalized trailing newline).
     */
    public function resolvedLlmsTxtBody(): string
    {
        if (($staging = CmsPublicSeo::resolvedStagingLlmsTxtBody()) !== null) {
            return $staging;
        }

        $default = CmsPublicSeo::LLMS_TXT_DEFAULT;
        $raw = null;

        if (modularousConfig('cms_seo.llms.use_system_settings', true)) {
            $persisted = $this->persistedGlobalLlmsTxt();
            if ($persisted !== null) {
                $raw = trim($persisted);
                if ($raw === '') {
                    $raw = null;
                }
            }
        }

        if ($raw === null) {
            $raw = trim((string) modularousConfig('cms_seo.llms.global_llms_txt', $default));
        }

        if ($raw === '') {
            $raw = trim($default);
        }

        return $raw . "\n";
    }

    /**
     * Raw value from SiteSettings, or null when unset (use env/config in UI and public fallback).
     */
    public function persistedGlobalRobotsTxt(): ?string
    {
        if (! SiteSettings::forFrontend()->has('seo.robots_txt')) {
            return null;
        }

        $value = SiteSettings::forFrontend()->get('seo.robots_txt');

        return $value !== null ? (string) $value : null;
    }

    /**
     * Raw value from SiteSettings, or null when unset (use env/config in UI and public fallback).
     */
    public function persistedGlobalLlmsTxt(): ?string
    {
        if (! SiteSettings::forFrontend()->has('seo.llms_txt')) {
            return null;
        }

        $value = SiteSettings::forFrontend()->get('seo.llms_txt');

        return $value !== null ? (string) $value : null;
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

    /**
     * Text shown in the panel editor: DB value if set, otherwise the effective env default (without forcing trailing newline).
     */
    public function globalLlmsTxtForEditor(): string
    {
        if (($staging = CmsPublicSeo::resolvedStagingLlmsTxtBody()) !== null) {
            return rtrim($staging, "\r\n");
        }

        $persisted = $this->persistedGlobalLlmsTxt();
        if ($persisted !== null) {
            return rtrim($persisted, "\r\n");
        }

        $default = CmsPublicSeo::LLMS_TXT_DEFAULT;
        $raw = trim((string) modularousConfig('cms_seo.llms.global_llms_txt', $default));
        if ($raw === '') {
            $raw = trim($default);
        }

        return $raw;
    }

    public function saveGlobalRobotsTxt(?string $value): void
    {
        $normalized = $value === null || trim($value) === '' ? null : rtrim($value, "\r\n");

        // Panel SEO tool writes the system-wide default; CMS Site Settings can override per frontend.
        SiteSettings::forBackend()->set('seo.robots_txt', $normalized);
    }

    public function saveGlobalLlmsTxt(?string $value): void
    {
        $normalized = $value === null || trim($value) === '' ? null : rtrim($value, "\r\n");

        SiteSettings::forBackend()->set('seo.llms_txt', $normalized);
    }
}
