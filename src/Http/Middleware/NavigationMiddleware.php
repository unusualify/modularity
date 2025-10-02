<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Facades\Navigation;

class NavigationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        view()->composer([
            modularityBaseKey() . '::layouts.*',
            'translation::layout'
        ], function ($view) {
            $view->with('navigation', get_modularity_navigation_config());
        });

        return $next($request);
    }
}
