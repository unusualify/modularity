<?php

namespace Unusualify\Modularous\Http\Controllers\Utility;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Modules\SystemUser\Repositories\UserRepository;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Http\Controllers\Controller;

class ImpersonateController extends Controller
{
    /**
     * Max number of recently impersonated users tracked in the session.
     */
    protected const RECENT_IMPERSONATIONS_LIMIT = 5;

    /**
     * Session key holding the recent impersonation id stack (newest first).
     */
    protected const RECENT_IMPERSONATIONS_KEY = 'impersonate_recent';

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
     * @return RedirectResponse
     */
    public function impersonate($id, UserRepository $users)
    {
        if ($this->authManager->guard(Modularous::getAuthGuardName())->user()->can('impersonate')) {
            $user = $users->getById($id);
            $this->authManager->guard(Modularous::getAuthGuardName())->user()->setImpersonating($user->id);

            $this->pushRecentImpersonation((int) $user->id);
        }

        return back();
    }

    /**
     * Prepend the just-impersonated user id onto the recent stack, dedupe, cap.
     */
    protected function pushRecentImpersonation(int $id): void
    {
        $recent = \array_values(\array_filter(
            (array) Session::get(self::RECENT_IMPERSONATIONS_KEY, []),
            fn ($value) => \is_numeric($value) && (int) $value !== $id
        ));

        \array_unshift($recent, $id);

        Session::put(
            self::RECENT_IMPERSONATIONS_KEY,
            \array_slice($recent, 0, self::RECENT_IMPERSONATIONS_LIMIT)
        );
    }

    /**
     * @return RedirectResponse
     */
    public function stopImpersonate()
    {
        $this->authManager->guard(Modularous::getAuthGuardName())->user()->stopImpersonating();

        return back();
    }
}
