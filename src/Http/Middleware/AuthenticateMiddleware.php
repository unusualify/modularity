<?php

namespace Unusualify\Modularity\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Facades\Modularity;

class AuthenticateMiddleware extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {

        if (!$request->expectsJson()) {
            $modularityAdminRouteNamePrefix = Modularity::getAdminRouteNamePrefix();
            // Define auth routes that should not be stored as intended URL
            $excludedRoutes = Arr::map([
                'login.form', 'login', 'logout',
                'register.form', 'register', 'register.success',
                'password.reset.link', 'password.reset.email',
                'password.reset.success', 'password.reset',
                'password.reset.update',
                'impersonate.stop', 'impersonate'
            ], function ($route) use ($modularityAdminRouteNamePrefix) {
                return $modularityAdminRouteNamePrefix ? $modularityAdminRouteNamePrefix . '.' . $route : $route;
            });

            // Only store the previous URL if it's not an auth route
            if (!in_array($request->route()->getName(), $excludedRoutes)) {
                session()->put('url.intended', url()->previous());
            }
        }

        return route(Route::hasAdmin('login.form'));
        // return $request->expectsJson() ? null : route('login.create');
    }
}
