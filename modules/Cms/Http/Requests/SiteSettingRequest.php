<?php

namespace Modules\Cms\Http\Requests;

use Unusualify\Modularous\Http\Requests\Request;

class SiteSettingRequest extends Request
{
    public function rulesForAll()
    {
        return [];
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
