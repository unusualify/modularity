<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularity\Http\Controllers\BaseController;

class UserController extends BaseController
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


    protected $titleColumnKey = 'name';


    // protected $perPage = 2;


    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = "User";


    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
    }

    public function destroy($id, $submoduleId = null)
    {
        $params = $this->request->route()->parameters();

        $id = last($params);

        $item = $this->repository->getById($id);

        if($item->isSuperAdmin()){
            return $this->respondWithError(___("listing.delete-superadmin-error"));
        }

        if ($this->repository->delete($id)) {
            // $this->fireEvent();
            activity()->performedOn($item)->log('deleted');

            return $this->respondWithSuccess(___("listing.delete.success", ['modelTitle' => $this->modelTitle]));
            // return $this->respondWithSuccess(___("$this->baseKey::lang.listing.delete.success", ['modelTitle' => $this->modelTitle]));
        }

        return $this->respondWithError(___("listing.delete.error", ['modelTitle' => $this->modelTitle]));
        // return $this->respondWithError(unusualTrans("$this->baseKey::lang.listing.delete.error", ['modelTitle' => $this->modelTitle]));
    }
}
