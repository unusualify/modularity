<?php

namespace Modules\SystemPayment\Http\Controllers\Front;

use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;
use Illuminate\Contracts\Foundation\Application;

class CardTypeController extends BaseController
{
    /**
     * @var string
     */
    protected $moduleName = 'SystemPayment';

    /**
     * @var string
     */
    protected $routeName = 'CardType';

    /**
     * Use default authorization permissions
     *
     * @var bool
     */
    protected $setDefaultPermissions = false;


    public function __construct(
        Application $app,
        Request $request
    )
    {
        parent::__construct($app,$request);
    }
}