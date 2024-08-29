<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

class CompanyRegistrationMiddleware{

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
        // dd(auth()->user()->invalidCompany);
        // dd($request->routeIs('profile.*'), Route::hasAdmin('profile'));
        if (!($request->routeIs('profile.*') || $request->routeIs('profile') || Route::hasAdmin('profile'))) {
            if (auth()->user()->invalidCompany) {
                return redirect()->route(Route::hasAdmin('profile'));
            }
        }
        return $next($request);
    }
}
