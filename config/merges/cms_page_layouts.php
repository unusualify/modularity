<?php

return [
    /**
     * Presentation shell keyed by routed model FQCN only (locale-agnostic), {@see \Modules\Cms\Entities\PageLayout}.
     *
     * @see \Modules\Cms\Services\CmsPageLayoutResolver
     */
    'enabled' => env('MODULAROUS_CMS_PAGE_LAYOUTS_ENABLED', true),

    /**
     * @deprecated Full-screen Inertia composer removed; shell appends are edited inline via the layout-blades modal on the PageLayout form.
     */
    'layout_composer_enabled' => env('MODULAROUS_CMS_PAGE_LAYOUT_COMPOSER_ENABLED', false),

    /**
     * When {@code cms_layout_builder.preview_enabled} is false, still allow POST shell draft preview for
     * {@code page_layout_appends} (modal on the PageLayout form).
     */
    'layout_appends_modal_preview_enabled' => env('MODULAROUS_CMS_PAGE_LAYOUT_APPENDS_MODAL_PREVIEW_ENABLED', true),

    /**
     * When no enabled {@see PageLayout} row exists, still merge module {@code {route}/page_layout/{head,body,footer}.blade.php}
     * segments into the layout shell (same as {@code blade_source=filesystem} on a PageLayout binding).
     */
    'filesystem_segments_without_db_binding_enabled' => env('MODULAROUS_CMS_PAGE_LAYOUT_FILESYSTEM_WITHOUT_DB', true),

    /**
     * Fallback {@see LayoutBuilder} when a PageLayout row has no {@code layout_builder_id} and static segments need a shell.
     * Falls back to {@see cms_layout_builder.default_layout_slug} when unset.
     */
    'default_layout_builder_id' => env('MODULAROUS_CMS_PAGE_LAYOUT_DEFAULT_BUILDER_ID') ?: null,

    /**
     * When {@code module::route.custom} and static page_layout segments are missing, inject
     * {@see public_presentation_informational_fallback_view} as the inner body (wrapped when a default layout exists).
     */
    'public_presentation_informational_fallback_enabled' => env('MODULAROUS_CMS_PUBLIC_PRESENTATION_INFO_FALLBACK', true),

    /** Blade used for the informational inner body fallback above. */
    'public_presentation_informational_fallback_view' => env(
        'MODULAROUS_CMS_PUBLIC_PRESENTATION_INFO_FALLBACK_VIEW',
        'cms::page.page_layout.body',
    ),
];
