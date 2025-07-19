<?php

namespace Modules\SystemUser\Http\Controllers\API;

use Unusualify\Modularity\Http\Controllers\ApiController;

class UserController extends ApiController
{
    /**
     * @var string
     */
    protected $namespace = 'Modules\SystemUser';

    /**
     * @var string
     */
    protected $moduleName = 'SystemUser';

    /**
     * @var string
     */
    protected $routeName = 'User';

    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = 'User';

    /**
     * Available includes for this resource
     *
     * @var array
     */
    protected $availableIncludes = ['roles', 'permissions', 'profile'];

    /**
     * Available filters for this resource
     *
     * @var array
     */
    protected $availableFilters = ['role', 'status', 'email_verified'];

    /**
     * Available sorts for this resource
     *
     * @var array
     */
    protected $availableSorts = ['id', 'name', 'email', 'created_at', 'updated_at'];
}
