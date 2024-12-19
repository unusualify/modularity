<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Route;

class CompanyRegistrationMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    public function handle($request, Closure $next)
    {
        if (! ($request->routeIs('profile.*') || $request->routeIs('profile')) && Route::hasAdmin('profile')) {
            if (auth()->user()->invalidCompany) {
                return redirect()->route(Route::hasAdmin('profile'));
            }
        }

        return $next($request);
    }
}
