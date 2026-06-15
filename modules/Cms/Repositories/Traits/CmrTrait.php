<?php

namespace Modules\Cms\Repositories\Traits;

/**
 * Content module route (CMR): parent-segment repository behaviour + {@see UrlRoute} registry sync for panel routes.
 *
 * For {@see \Modules\Cms\Entities\Page}, {@see \Modules\Cms\Entities\HomepageTest}, and similar CMS module routes.
 */
trait CmrTrait
{
    use PageLayoutTrait,
        ParentSegmentTrait,
        UrlRouteRegistrySyncTrait;
}
