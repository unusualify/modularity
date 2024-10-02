<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;

class HostableMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
