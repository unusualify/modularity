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
            $user = auth()->user();
            $userRepository = app()->make(\Modules\SystemUser\Repositories\UserRepository::class);
            $profileShortcutSchema = modularity_format_inputs(getFormDraft('profile_shortcut'));
            $profileShortcutModel = $userRepository->getFormFields($user, $profileShortcutSchema);
            $loginShortcutSchema = modularity_format_inputs(getFormDraft('login_shortcut'));

            $view->with(array_merge($view->getData(), [
                'authorization' => get_modularity_authorization_config(),
                'profileShortcutSchema' => $profileShortcutSchema,
                'profileShortcutModel' => $profileShortcutModel,
                'loginShortcutModel' => [],
                'loginShortcutSchema' => $loginShortcutSchema,
            ]));
        });

        return $next($request);
    }
}
