<?php

return [
    // Infrastructure registration toggles for CMS facilities.
    // Feature behavior remains controlled by security/cms_promotion/cms_seo/cms_routing.
    'enabled' => env('MODULAROUS_CMS_FEATURES_ENABLED', true),
    'register_contracts' => env('MODULAROUS_CMS_REGISTER_CONTRACTS', true),
    'register_middlewares' => env('MODULAROUS_CMS_REGISTER_MIDDLEWARES', true),
    'error_pages_enabled' => env('MODULAROUS_CMS_ERROR_PAGES_ENABLED', true),
    // Model-scoped presentationItem HTML for published ErrorPage rows (see ErrorPagePresentationCache).
    'error_pages_cache_enabled' => env('MODULAROUS_CMS_ERROR_PAGES_CACHE_ENABLED', true),
];
