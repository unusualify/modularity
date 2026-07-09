<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Unusualify\Modularous\Facades\ModularousCache;

final class VerifyModularousCacheWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ModularousCache::isWebhookEnabled()) {
            abort(404);
        }

        $secret = ModularousCache::getWebhookSecret();
        if ($secret === null) {
            abort(503, 'Webhook secret is not configured.');
        }

        $signature = $request->header('X-Modularous-Signature');
        if (! is_string($signature) || ! str_starts_with($signature, 'sha256=')) {
            abort(401, 'Invalid signature header.');
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        if (! hash_equals($expected, $signature)) {
            abort(401, 'Signature mismatch.');
        }

        return $next($request);
    }
}
