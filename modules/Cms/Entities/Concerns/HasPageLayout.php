<?php

namespace Modules\Cms\Entities\Concerns;

use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Repositories\Traits\PageLayoutTrait;

/**
 * Marks a CMS module-route model as having an optional locale-agnostic presentation shell ({@see PageLayout}).
 *
 * Wired from {@see IsCmr} for content routes; repositories use {@see PageLayoutTrait}.
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
