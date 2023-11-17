<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class ImpersonateMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    /**
     * Handles an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->session()->has('impersonate')) {
            $this->authFactory->guard('unusual_users')->onceUsingId($request->session()->get('impersonate'));
        }

        view()->composer(unusualBaseKey()."::layouts.master", function ($view)
        {
            $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);
            $users = $userRepository->whereNot('id', 1)->get();

            $configuration = [
                'active' => auth()->user()->isSuperAdmin() || auth()->user()->isImpersonating(),
                'users' =>  $users,
                'impersonated' => auth()->user()->isImpersonating(),
                'stopRoute' => route('impersonate.stop'),
                'route' => route('impersonate', ['id' => ':id'])
            ];
            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('impersonateConfiguration', $configuration);
        });


        return $next($request);
    }
}
