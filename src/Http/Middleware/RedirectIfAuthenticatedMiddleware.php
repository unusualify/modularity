<?php

namespace Unusualify\Modularous\Http\Middleware;

use Closure;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Unusualify\Modularous\Services\DevAuth\DevAutoLoginService;

class RedirectIfAuthenticatedMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    /**
     * @var Redirector
     */
    protected $redirector;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(AuthFactory $authFactory, Redirector $redirector, Config $config)
    {
        $this->authFactory = $authFactory;
        $this->redirector = $redirector;
        $this->config = $config;
    }

    /**
     * Handles an incoming request.
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'modularous')
    {
        if ($this->authFactory->guard($guard)->check() || $this->devAutoLoginApplies()) {
            return $this->redirector->to(modularousConfig('auth_login_redirect_path', '/'));
        }

        return $next($request);
    }

    /**
     * Guest pages (login, register…) are skipped when the local dev auto-login would
     * authenticate the panel anyway.
     */
    protected function devAutoLoginApplies(): bool
    {
        $devAutoLogin = app(DevAutoLoginService::class);

        return $devAutoLogin->isEnabled() && $devAutoLogin->resolveUserId() !== null;
    }
}
