<?php

return [
    /**
     * Default when a row omits {@code blade_source}: {@code db} stores segments in DB; {@code filesystem} uses {@code blade_view_name}.
     */
    'default_blade_source' => env('MODULAROUS_CMS_LAYOUT_BLADE_SOURCE', 'db'),

    /**
     * Maximum total bytes for all DB segments combined (head+body+footer), UTF-8 counted.
     */
    'max_blade_segments_bytes' => (int) env('MODULAROUS_CMS_LAYOUT_SEGMENTS_MAX_BYTES', 512_000),

    /**
     * Allow admin HTML preview iframe route (session / web panel).
     */
    'preview_enabled' => (bool) env('MODULAROUS_CMS_LAYOUT_PREVIEW_ENABLED', true),

    /**
     * When true, {@see \Modules\Cms\Http\Middleware\LayoutBuilderMiddleware} runs (opt-in; register on route groups in the host app).
     */
    'middleware_enabled' => (bool) env('MODULAROUS_CMS_LAYOUT_MIDDLEWARE_ENABLED', false),

    /**
     * Query string key to resolve a layout by slug (middleware).
     */
    'middleware_query_slug_key' => env('MODULAROUS_CMS_LAYOUT_QUERY_KEY', 'cms_layout'),

    /** Optional default slug when no query param present. */
    'default_layout_slug' => env('MODULAROUS_CMS_LAYOUT_DEFAULT_SLUG', ''),
];
