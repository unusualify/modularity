<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Routing\Redirector;

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
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'modularity')
    {
        if ($this->authFactory->guard($guard)->check()) {
            return $this->redirector->to($this->config->get('unusual.auth_login_redirect_path', '/'));
        }

        return $next($request);
    }
}
