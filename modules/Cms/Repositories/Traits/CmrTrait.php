<?php

namespace Modules\Cms\Repositories\Traits;

use Modules\Cms\Entities\HomepageTest;
use Modules\Cms\Entities\Page;

/**
 * Content module route (CMR): parent-segment repository behaviour + {@see UrlRoute} registry sync for panel routes.
 *
 * For {@see Page}, {@see HomepageTest}, and similar CMS module routes.
 */
trait CmrTrait
{
    use PageLayoutTrait,
        ParentSegmentTrait,
        UrlRouteRegistrySyncTrait;
}
