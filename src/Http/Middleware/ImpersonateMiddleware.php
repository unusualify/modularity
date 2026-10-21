<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Unusualify\Modularous\Facades\Modularous;

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
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->hasSession() && $request->session()->has('impersonate')) {
            $guard = $this->authFactory->guard(Modularous::getAuthGuardName());

            $request->attributes->set('impersonator', $guard->user());
            $guard->onceUsingId($request->session()->get('impersonate'));
        }

        view()->composer(modularousBaseKey() . '::layouts.master', function ($view) {
            $view->with('impersonation', get_modularous_impersonation_config());
        });

        return $next($request);
    }
}
