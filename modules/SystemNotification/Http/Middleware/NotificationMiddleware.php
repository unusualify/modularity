<?php

namespace Modules\SystemNotification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotificationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
