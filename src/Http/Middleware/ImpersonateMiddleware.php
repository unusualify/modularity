<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;

class ImpersonateMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->session()->has('impersonate')) {
            $this->authFactory->guard(Modularity::getAuthGuardName())->onceUsingId($request->session()->get('impersonate'));
        }

        view()->composer(modularityBaseKey() . '::layouts.master', function ($view) {
            $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);
            $activeUser = null;
            $canFetchUsers = false;

            if (Auth::check()) {
                $activeUser = Auth::user();
                $canFetchUsers = $activeUser->isSuperAdmin() || $activeUser->isImpersonating();
            }


            $impersonation = [
                'active' => $activeUser ? $activeUser->isSuperAdmin() || $activeUser->isImpersonating() : false,
                'users' => $canFetchUsers ? $userRepository->whereNot(fn($query) => $query->role(['superadmin']))->get(['id', 'name', 'email', 'company_id'])->toArray() : [],
                'impersonated' => $activeUser ? $activeUser->isImpersonating() : false,
                'stopRoute' => route(Route::hasAdmin('impersonate.stop')),
                'route' => route(Route::hasAdmin('impersonate'), ['id' => ':id']),
            ];
            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('impersonation', $impersonation);
        });

        return $next($request);
    }
}
