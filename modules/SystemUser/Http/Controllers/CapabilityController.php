<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Unusualify\Modularous\Http\Controllers\BaseController;

class CapabilityController extends BaseController
{
    protected $namespace = 'Modules\SystemUser';

    protected $moduleName = 'SystemUser';

    protected $routeName = 'Capability';

    protected $modelName = 'Capability';

    protected $titleColumnKey = 'name';

    public function __construct(Application $app, Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }
}
