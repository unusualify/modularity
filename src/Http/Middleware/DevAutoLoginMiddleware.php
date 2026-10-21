<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;

/**
 * Authenticates guests as the configured superadmin for the current request
 * only (no login session is written). Registered in `web.auth` between `web`
 * and `modularous.auth` when MODULAROUS_DEV_AUTO_LOGIN is on.
 */
class DevAutoLoginMiddleware
{
    public const HEADER = 'X-Dev-Auto-Login';

    public function __construct(
        protected AuthFactory $authFactory,
        protected DevAutoLoginService $devAutoLogin,
    ) {}

    /**
     * @param Request $request
     */
    public function handle($request, Closure $next): Response
    {
        $devUser = null;

        if ($this->devAutoLogin->isEnabled()) {
            $guard = $this->authFactory->guard(Modularous::getAuthGuardName());

            if ($guard->guest() && ($userId = $this->devAutoLogin->resolveUserId()) !== null) {
                $devUser = $guard->onceUsingId($userId) ?: null;
            }
        }

        $response = $next($request);

        if ($devUser) {
            $response->headers->set(self::HEADER, (string) ($devUser->email ?? $devUser->getAuthIdentifier()));
        }

        return $response;
    }
}
