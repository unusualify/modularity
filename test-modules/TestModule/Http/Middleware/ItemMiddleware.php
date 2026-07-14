<?php

namespace TestModules\TestModule\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ItemMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
