<?php

return [
    /**
     * Default namespace for CMS utility classes (e.g. {@code u-} → {@code .u-w-full}).
     * Per sheet: {@code definition.utilities.class_prefix} (or legacy {@code utility_class_prefix}) overrides this.
     * Empty string yields unprefixed tokens (e.g. {@code .w-full}); collision risk with host CSS / frameworks.
     */
    'utilities_class_prefix' => (string) env('MODULAROUS_CMS_STYLESHEET_UTILITIES_PREFIX', 'u-'),

    /**
     * Disk (see {@code config/filesystems.php}) where compiled custom CSS bundles are stored.
     */
    'compiled_disk' => env('MODULAROUS_CMS_STYLESHEET_DISK', 'local'),

    /**
     * Directory on that disk, without leading/trailing slashes.
     */
    'compiled_directory' => env('MODULAROUS_CMS_STYLESHEET_DIR', 'modularous/cms/stylesheets'),

    /**
     * Hard cap for {@code definition.raw_css} (bytes) when persisting.
     */
    'max_raw_css_bytes' => (int) env('MODULAROUS_CMS_STYLESHEET_MAX_RAW_CSS', 512_000),

    /**
     * When true and {@code scssphp/scssphp} is installed, {@code scss_source} is compiled into the bundle.
     */
    'scssphp' => [
        'enabled' => env('MODULAROUS_CMS_STYLESHEET_SCSSPHP', false),
    ],

    /**
     * Optional: map Bootstrap semver string → app-relative or absolute URL for {@code framework_source=vendor}.
     *
     * Demo files ship under {@code public/vendor/cms-stylesheet-examples/bootstrap/5.3.3/...} (see repository).
     *
     * @var array<string, string>
     */
    'bootstrap' => [
        'vendor_css_by_version' => [
            '5.3.3' => '/vendor/cms-stylesheet-examples/bootstrap/5.3.3/dist/css/bootstrap.min.css',
        ],
        /** Used when the version key is missing or {@code framework.bootstrap_css_href} is not set. */
        'vendor_css_href_fallback' => '/vendor/cms-stylesheet-examples/bootstrap/5.3.3/dist/css/bootstrap.min.css',

        /**
         * Bootstrap JS bundle (includes Popper). Same resolution order as CSS vendor: version map →
         * {@code definition.framework.bootstrap_bundle_js_href} → fallback.
         *
         * @var array<string, string>
         */
        'vendor_bundle_js_by_version' => [
            '5.3.3' => '/vendor/cms-stylesheet-examples/bootstrap/5.3.3/dist/js/bootstrap.bundle.min.js',
        ],
        'vendor_bundle_js_href_fallback' => '/vendor/cms-stylesheet-examples/bootstrap/5.3.3/dist/js/bootstrap.bundle.min.js',
    ],

    /**
     * Optional href used when {@code driver=tailwind} and {@code framework_source=cdn} (Tailwind has no full utility CDN).
     *
     * {@code build_css_href_fallback}: used for {@code framework_source=build} or {@code vendor} when
     * {@code definition.framework.tailwind_css_href} is empty. Demo file: {@code public/build/cms-stylesheet-examples/tailwind/tailwind-example.css}.
     */
    'tailwind' => [
        'placeholder_cdn_href' => env('MODULAROUS_CMS_STYLESHEET_TAILWIND_PLACEHOLDER_HREF', ''),
        'build_css_href_fallback' => '/build/cms-stylesheet-examples/tailwind/tailwind-example.css',
    ],

    /**
     * Max bytes read when {@code definition.framework.inline_vendor_bootstrap_into_bundle} prepends a vendor file.
     */
    'max_inline_vendor_css_bytes' => (int) env('MODULAROUS_CMS_STYLESHEET_MAX_INLINE_VENDOR', 2_000_000),

    /**
     * When true, Bootstrap CDN CSS may be fetched at compile time for inlining (off by default; prefer local {@code /} paths).
     */
    'allow_http_fetch_for_inline_merge' => (bool) env('MODULAROUS_CMS_STYLESHEET_FETCH_INLINE', false),

    'public_route' => [
        'enabled' => env('MODULAROUS_CMS_STYLESHEET_PUBLIC_ENABLED', true),
        /** Path prefix without leading slash; must stay in sync with catch-all excludes. */
        'path_prefix' => trim((string) env('MODULAROUS_CMS_STYLESHEET_PUBLIC_PREFIX', 'cms/stylesheets'), '/'),
        'max_age_seconds' => max(0, (int) env('MODULAROUS_CMS_STYLESHEET_CACHE_MAX_AGE', 3600)),
        /**
         * When true, the public bundle {@code <link href>} includes {@code ?<param>=<sha256>} derived from the current
         * compiled inline CSS so CDN/browser caches invalidate when sheet output changes (Vite-style hashed assets, query form).
         */
        'cache_bust_query' => (bool) env('MODULAROUS_CMS_STYLESHEET_URL_CACHE_BUST', true),
        /** Query key for {@code cache_bust_query} (letters, digits, underscore, hyphen only after sanitization). */
        'cache_bust_param' => (string) env('MODULAROUS_CMS_STYLESHEET_URL_CACHE_BUST_PARAM', 'v'),
    ],
];
