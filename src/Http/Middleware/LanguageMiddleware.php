<?php

namespace OoBook\CRM\Base\Http\Middleware;

use Closure;

class LanguageMiddleware
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
        if ($request->user()->language) {
            config([getUnusualBaseKey() . '.locale' => $request->user()->language]);
        }

        return $next($request);
    }
}
