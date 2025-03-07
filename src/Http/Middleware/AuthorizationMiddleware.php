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
        view()->composer(modularityBaseKey() . '::layouts.master', function ($view) {
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

            $defaultInput = modularity_default_input();

            $user = auth()->user();
            $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);

            $profileShortcutDraft = getFormDraft('profile_shortcut');

            $profileShortcutSchema = collect($profileShortcutDraft)->mapWithKeys(function ($v, $k) use ($defaultInput) {
                return [$k => configure_input(hydrate_input(array_merge($defaultInput, $v)))];
            })->toArray();

            $profileShortcutModel = $userRepository->getFormFields($user, $profileShortcutSchema);

            $loginShortcutSchema = getFormDraft('login_shortcut');
            $loginShortcutSchema = collect($loginShortcutSchema)->mapWithKeys(function ($v, $k) use ($defaultInput) {
                return [$k => configure_input(hydrate_input(array_merge($defaultInput, $v)))];
            })->toArray();
            // $loginShortcutModel = $userRepository->getFormFields($user, $loginShortcutSchema);
            // dd($loginShortcutSchema);

            $view->with('profileShortcutModel', $profileShortcutModel);
            $view->with('profileShortcutSchema', $profileShortcutSchema);
            $view->with('authorization', $authorization);
            $view->with('loginShortcutModel', []);
            $view->with('loginShortcutSchema', $loginShortcutSchema);
        });

        return $next($request);
    }
}
