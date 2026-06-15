<?php

namespace Modules\Cms\Http\Controllers\Front;

use Modules\Cms\Entities\Page;

/**
 * Public CMS {@see Page} renderer (invokable). View: {@code cms::page.custom}.
 */
final class PageController extends CmsController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Page';
}
