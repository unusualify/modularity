<?php

namespace Unusualify\Modularity\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthorizationMiddleware
{
    /**
     * @var AuthFactory
     */
    protected $authFactory;

    public function __construct(AuthFactory $authFactory)
    {
        $this->authFactory = $authFactory;
    }

    public function handle($request, Closure $next)
    {
        view()->composer(unusualBaseKey() . '::layouts.master', function ($view) {
            $user = Auth::user();

            $permissions = Arr::mapWithKeys(Gate::abilities(), function ($closure, $key) {
                return [$key => Gate::allows($key)];
            });

            $roles = Arr::map($user->roles->toArray(), function ($role) {
                return $role['name'];
            });

            $authorization = [
                'isSuperAdmin' => $user->isSuperAdmin(),
                'isClient' => $user->isClient(),
                'roles' => $roles,
                'permissions' => $permissions,

                // 'hasRestorable' => $user->isSuperAdmin(),
                // 'hasBulkable' => $user->isSuperAdmin(),
            ];
            // setActiveMenuItem($configuration['sidebar'], $configuration['current_url']);
            $view->with('authorization', $authorization);
        });

        return $next($request);
    }
}
