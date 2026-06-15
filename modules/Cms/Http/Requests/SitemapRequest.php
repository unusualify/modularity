<?php

namespace Modules\Cms\Http\Requests;

use Modules\Cms\Http\Controllers\CmsSitemapPanelController;
use Unusualify\Modularous\Http\Requests\Request;

/**
 * Panel JSON POST for {@see CmsSitemapPanelController} (body genelde boş; ileri alanlar için genişletilebilir).
 */
class SitemapRequest extends Request
{
    public function rulesForAll()
    {
        return [
            // Reserved for future: 'force' => 'sometimes|boolean',
        ];
    }

    public function rulesForCreate()
    {
        return [];
    }

    public function rulesForUpdate()
    {
        return [];
    }
}
