<?php

use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Entities\ParentSegment;
use Modules\Cms\Services\CmsParentSegmentResolver;
use Modules\Cms\Support\ParentSegmentBindingValidator;

return [
    /**
     * URL parent prefix bindings: one row per model class + locale (unique on target_model_class + locale).
     * {@code normalized_prefix} may be left blank for a locale-root homepage (see {@see ParentSegmentBindingValidator} exclusivity rule).
     *
     * Presentation shells (locale-agnostic) are handled by {@see PageLayout}, not ParentSegment rows.
     *
     * @see CmsParentSegmentResolver
     * @see ParentSegment
     */
    'enabled' => env('MODULAROUS_CMS_PARENT_SEGMENTS_ENABLED', true),
];
