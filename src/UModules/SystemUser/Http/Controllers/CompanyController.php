<?php

namespace Modules\SystemUser\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Unusualify\Modularity\Http\Controllers\BaseController;

class CompanyController extends BaseController
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
    protected $routeName = 'Company';


    protected $titleColumnKey = 'name';


    // protected $perPage = 2;


    /**
     * @var string
     */
    // protected $routePrefix = 'User';

    /**
     * @var string
     */
    protected $modelName = "Company";


    public function __construct(\Illuminate\Foundation\Application $app,Request $request)
    {
        parent::__construct(
            $app,
            $request
        );
        // dd(
        //     $this->request->user(),
        //     auth()->user()
        // );
        // dd(
        //     // $this->repository->getById(1, ['role']),
        //     // $this->repository->getFormFields(
        //     //     $this->repository->getById(1, ['roles']),
        //     //     $this->getFormSchema($this->getConfigFieldsByRoute('inputs'))
        //     // )
        // );
    }
}
