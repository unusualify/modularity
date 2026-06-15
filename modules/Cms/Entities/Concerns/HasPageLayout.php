<?php

namespace Modules\Cms\Entities\Concerns;

/**
 * Marks a CMS module-route model as having an optional locale-agnostic presentation shell ({@see \Modules\Cms\Entities\PageLayout}).
 *
 * Wired from {@see IsCmr} for content routes; repositories use {@see \Modules\Cms\Repositories\Traits\PageLayoutTrait}.
 *
 * Differs from {@see HasParentSegment}: URLs remain per-model + locale bindings; Blade shell + append fragments are one row per model class only.
 */
trait HasPageLayout
{
    public static function supportsPageLayoutBindings(): bool
    {
        return true;
    }
}
