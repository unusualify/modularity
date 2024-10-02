<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Modules\SystemUser\Repositories\UserRepository;

class ImpersonateController extends Controller
{
    // /**
    //  * @var AuthManager
    //  */
    // protected $authManager;

    public function __construct(protected AuthManager $authManager)
    {
        parent::__construct();

        // $this->authManager = $authManager;
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id, UserRepository $users)
    {
        if ($this->authManager->guard('unusual_users')->user()->can('impersonate')) {
            $user = $users->getById($id);
            $this->authManager->guard('unusual_users')->user()->setImpersonating($user->id);
        }

        return back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonate()
    {
        $this->authManager->guard('unusual_users')->user()->stopImpersonating();

        return back();
    }
}
