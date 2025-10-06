<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
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
            $view->with('impersonation', get_modularity_impersonation_config());
        });

        return $next($request);
    }
}
