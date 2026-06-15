<?php

return [
    /**
     * Staging / pre-production: force {@code noindex, nofollow} on every public CMS page and serve
     * {@code Disallow: /} at GET /robots.txt ({@see \Modules\Cms\Support\CmsPublicSeo},
     * {@see \Modules\Cms\Http\Controllers\Front\CmsController},
     * {@see \Modules\Cms\Http\Controllers\Front\RobotsTxtController}).
     */
    'staging' => [
        'force_noindex' => env('MODULAROUS_CMS_SEO_STAGING_FORCE_NOINDEX', false),
    ],

    /**
     * Public URL normalization used by {@see \Modules\Cms\Services\CanonicalUrlResolver}.
     */
    'canonical' => [
        'force_lowercase_path' => env('MODULAROUS_CMS_SEO_CANONICAL_FORCE_LOWERCASE', true),
        'trim_trailing_slash' => env('MODULAROUS_CMS_SEO_CANONICAL_TRIM_TRAILING_SLASH', true),
    ],

    /**
     * Global robots.txt (served at GET /robots.txt when route enabled).
     *
     * @see \Modules\Cms\Http\Controllers\Front\RobotsTxtController
     */
    'robots' => [
        'route_enabled' => env('MODULAROUS_CMS_ROBOTS_TXT_ROUTE_ENABLED', true),
        'global_robots_txt' => env('MODULAROUS_CMS_SEO_GLOBAL_ROBOTS_TXT', ''),
        /**
         * When true, GET /robots.txt prefers {@see \Modules\Cms\Services\CmsSiteSeoSettingsService} (um_cms_site_settings).
         * When false, only env/config {@code global_robots_txt} is used (legacy / headless deploys).
         */
        'use_site_settings' => env('MODULAROUS_CMS_SEO_ROBOTS_USE_SITE_SETTINGS', true),
        /**
         * Composite key for the global robots.txt body row (must match unique index on site_settings).
         */
        'site_setting' => [
            'group_key' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_GROUP', 'seo'),
            'key' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_KEY', 'global_robots_txt'),
            'locale' => env('MODULAROUS_CMS_SEO_ROBOTS_SITE_LOCALE', '*'),
        ],
    ],

    /**
     * Panel: soft checks when saving a published {@see \Modules\Cms\Entities\Page}.
     *
     * @see \Modules\Cms\Services\CmsAdminWarnings
     */
    'admin' => [
        'publish_soft_warnings' => env('MODULAROUS_CMS_ADMIN_SEO_PUBLISH_SOFT_WARNINGS', true),
        /**
         * When true, saving a published {@see \Modules\Cms\Entities\Page} shows a soft warning if "now" is outside the optional publish window
         * (visitors already get 404 via {@see \Unusualify\Modularous\Entities\Traits\Core\HasScopes::scopeVisible} on public routes).
         */
        'publish_schedule_warnings' => env('MODULAROUS_CMS_ADMIN_PUBLISH_SCHEDULE_WARNINGS', true),
    ],
];
