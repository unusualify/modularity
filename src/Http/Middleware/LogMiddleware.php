<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Unusualify\Modularity\Facades\ModularityLog;

class LogMiddleware
{
    public function handle($request, Closure $next)
    {
        $requestId = (string) Str::uuid();

        ModularityLog::withContext([
            'request_id' => $requestId,
        ]);

        $response = $next($request);

        $response->headers->set('Request-Id', $requestId);

        return $response;
    }
}
