<?php

namespace Modules\Cms\Http\Controllers;

use Modules\Cms\Routes\web;
use Unusualify\Modularous\Http\Controllers\BaseController;

/**
 * Session-backed JSON for panel (dry-run / commit), aligned with {@see web} pattern.
 */
class CmsSitemapPanelController extends BaseController
{
    protected $moduleName = 'Cms';

    protected $routeName = 'Sitemap';
}
