<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;

class HostableMiddleware
{
    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
