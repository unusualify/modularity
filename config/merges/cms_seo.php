<?php

use Modules\Cms\Entities\Page;
use Modules\Cms\Http\Controllers\Front\CmsController;
use Modules\Cms\Http\Controllers\Front\LlmsTxtController;
use Modules\Cms\Http\Controllers\Front\RobotsTxtController;
use Modules\Cms\Services\CanonicalUrlResolver;
use Modules\Cms\Services\CmsAdminWarnings;
use Modules\Cms\Services\CmsSiteSeoSettingsService;
use Modules\Cms\Support\CmsPublicSeo;
use Modules\SystemSetting\Support\MigrateRobotsTxtFromSiteSetting;
use Unusualify\Modularous\Entities\Traits\Core\HasScopes;
use Unusualify\Modularous\Facades\SiteSettings;

return [
    /**
     * Staging / pre-production: force {@code noindex, nofollow} on every public CMS page and serve
     * {@code Disallow: /} at GET /robots.txt and a staging stub at GET /llms.txt ({@see CmsPublicSeo},
     * {@see CmsController},
     * {@see RobotsTxtController},
     * {@see LlmsTxtController}).
     */
    'staging' => [
        'force_noindex' => env('MODULAROUS_CMS_SEO_STAGING_FORCE_NOINDEX', false),
    ],

    /**
     * Public URL normalization used by {@see CanonicalUrlResolver}.
     */
    'canonical' => [
        'force_lowercase_path' => env('MODULAROUS_CMS_SEO_CANONICAL_FORCE_LOWERCASE', true),
        'trim_trailing_slash' => env('MODULAROUS_CMS_SEO_CANONICAL_TRIM_TRAILING_SLASH', true),
    ],

    /**
     * Global robots.txt (served at GET /robots.txt when route enabled).
     *
     * @see RobotsTxtController
     * @see CmsSiteSeoSettingsService
     */
    'robots' => [
        'route_enabled' => env('MODULAROUS_CMS_ROBOTS_TXT_ROUTE_ENABLED', true),
        'global_robots_txt' => env('MODULAROUS_CMS_SEO_GLOBAL_ROBOTS_TXT', ''),
        /**
         * When true, GET /robots.txt prefers {@see CmsSiteSeoSettingsService}
         * ({@see SiteSettings} → CmsSettings with SystemSettings fallback).
         * When false, only env/config {@code global_robots_txt} is used (legacy / headless deploys).
         */
        'use_system_settings' => env('MODULAROUS_CMS_SEO_ROBOTS_USE_SYSTEM_SETTINGS', env('MODULAROUS_CMS_SEO_ROBOTS_USE_SITE_SETTINGS', true)),
        /**
         * Legacy KV row keys used only by {@see MigrateRobotsTxtFromSiteSetting}.
         *
         * @deprecated Robots.txt is stored on SystemSetting General / Cms SiteSetting (IsSingular).
         */
        'legacy_site_setting' => [
            'group_key' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_GROUP', 'seo'),
            'key' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_KEY', 'global_robots_txt'),
            'locale' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_LOCALE', '*'),
        ],
    ],

    /**
     * Global llms.txt (served at GET /llms.txt when route enabled). Spec: https://llmstxt.org/
     *
     * Host apps opt in via {@code route_enabled} (package default false).
     *
     * @see LlmsTxtController
     * @see CmsSiteSeoSettingsService
     */
    'llms' => [
        'route_enabled' => env('MODULAROUS_CMS_LLMS_TXT_ROUTE_ENABLED', false),
        'global_llms_txt' => env('MODULAROUS_CMS_SEO_GLOBAL_LLMS_TXT', ''),
        /**
         * When true, GET /llms.txt prefers {@see CmsSiteSeoSettingsService}
         * ({@see SiteSettings} → CmsSettings with SystemSettings fallback).
         */
        'use_system_settings' => env('MODULAROUS_CMS_SEO_LLMS_USE_SYSTEM_SETTINGS', true),
    ],

    /**
     * Panel: soft checks when saving a published {@see Page}.
     *
     * @see CmsAdminWarnings
     */
    'admin' => [
        'publish_soft_warnings' => env('MODULAROUS_CMS_ADMIN_SEO_PUBLISH_SOFT_WARNINGS', true),
        /**
         * When true, saving a published {@see Page} shows a soft warning if "now" is outside the optional publish window
         * (visitors already get 404 via {@see HasScopes::scopeVisible} on public routes).
         */
        'publish_schedule_warnings' => env('MODULAROUS_CMS_ADMIN_PUBLISH_SCHEDULE_WARNINGS', true),
    ],
];
